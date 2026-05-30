<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Stock;

class CostCalculationService
{
    /**
     * Изчислява себестойност на продукт при нова покупка (FIFO)
     * 
     * @param int $variantId
     * @param int $storageObjectId
     * @param int $newQuantity
     * @param float $newUnitCost
     * @return array
     */
    public static function calculateNewAverageCost(int $variantId, int $storageObjectId, int $newQuantity, float $newUnitCost): array
    {
        $stock = Stock::where('product_variant_id', $variantId)
            ->where('storage_object_id', $storageObjectId)
            ->first();
        
        $currentQuantity = $stock ? $stock->quantity : 0;
        $currentAverageCost = $stock ? $stock->average_cost : 0;
        
        if ($currentQuantity == 0) {
            $newAverageCost = $newUnitCost;
        } else {
            $totalCost = ($currentQuantity * $currentAverageCost) + ($newQuantity * $newUnitCost);
            $totalQuantity = $currentQuantity + $newQuantity;
            $newAverageCost = $totalCost / $totalQuantity;
        }
        
        return [
            'old_quantity' => $currentQuantity,
            'old_average_cost' => $currentAverageCost,
            'new_quantity' => $currentQuantity + $newQuantity,
            'new_average_cost' => round($newAverageCost, 2),
            'cost_difference' => round($newAverageCost - $currentAverageCost, 2),
        ];
    }
    
    /**
     * Разпределя транспортните разходи върху артикулите
     */
    public static function distributeDeliveryCost(array $items, float $totalDeliveryCost): array
    {
        $totalValue = collect($items)->sum('total_cost');
        
        if ($totalValue == 0) {
            return $items;
        }
        
        foreach ($items as &$item) {
            $share = ($item['total_cost'] / $totalValue) * $totalDeliveryCost;
            $item['delivery_cost_share'] = round($share, 2);
            $item['final_unit_cost'] = round($item['unit_cost'] + ($share / $item['quantity']), 2);
        }
        
        return $items;
    }
    
    /**
     * Добавя нова cost layer за FIFO
     */
    public static function addCostLayer(int $variantId, int $storageObjectId, int $quantity, float $unitCost): void
    {
        $stock = Stock::firstOrCreate(
            [
                'product_variant_id' => $variantId,
                'storage_object_id' => $storageObjectId,
            ],
            [
                'quantity' => 0,
                'average_cost' => 0,
                'cost_layers' => [],
                'reserved_quantity' => 0,
                'min_quantity' => 0,
            ]
        );
        
        $layers = $stock->cost_layers ?? [];
        
        // Добавяне на нов слой
        $layers[] = [
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'remaining' => $quantity,
            'purchased_at' => now()->toDateTimeString(),
        ];
        
        $stock->cost_layers = $layers;
        $stock->save();
    }
    
    /**
     * Взема себестойност на определено количество (FIFO)
     */
    public static function getCostFromLayers(int $variantId, int $storageObjectId, int $quantity): float
    {
        $stock = Stock::where('product_variant_id', $variantId)
            ->where('storage_object_id', $storageObjectId)
            ->first();
        
        if (!$stock || !$stock->cost_layers) {
            return 0;
        }
        
        $layers = $stock->cost_layers;
        $remainingQty = $quantity;
        $totalCost = 0;
        
        foreach ($layers as &$layer) {
            if ($remainingQty <= 0) break;
            
            $takeQty = min($layer['remaining'], $remainingQty);
            $totalCost += $takeQty * $layer['unit_cost'];
            $layer['remaining'] -= $takeQty;
            $remainingQty -= $takeQty;
        }
        
        // Премахване на празните слоеве
        $stock->cost_layers = array_filter($layers, fn($l) => $l['remaining'] > 0);
        $stock->save();
        
        return $remainingQty > 0 ? $totalCost / $quantity : $totalCost / $quantity;
    }
}