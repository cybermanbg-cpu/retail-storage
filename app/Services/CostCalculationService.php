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
     * @param float $newQuantity
     * @param float $newUnitCost
     * @return array
     */
    public static function calculateNewAverageCost(int $variantId, int $storageObjectId, float $newQuantity, float $newUnitCost): array
    {
        $stock = Stock::where('product_variant_id', $variantId)
            ->where('storage_object_id', $storageObjectId)
            ->first();

        $currentQuantity = $stock ? floatval($stock->quantity) : 0;
        $currentAverageCost = $stock ? floatval($stock->average_cost) : 0;

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
            'new_average_cost' => round($newAverageCost, 4),
            'cost_difference' => round($newAverageCost - $currentAverageCost, 4),
        ];
    }

    /**
     * Разпределя транспортните разходи върху артикулите
     */
    public static function distributeDeliveryCost(array $items, float $totalDeliveryCost): array
    {
        \Log::info('distributeDeliveryCost called with items:', $items);

        $totalValue = 0;
        foreach ($items as $item) {
            $totalValue += floatval($item['total_cost']);
        }

        \Log::info('Total value: ' . $totalValue);

        if ($totalValue == 0) {
            \Log::warning('Total value is 0, returning original items');
            return $items;
        }

        foreach ($items as &$item) {
            $share = (floatval($item['total_cost']) / $totalValue) * $totalDeliveryCost;
            $item['delivery_cost_share'] = round($share, 4);
            $item['final_unit_cost'] = round(floatval($item['unit_cost']) + ($share / floatval($item['quantity'])), 4);
        }

        \Log::info('distributeDeliveryCost result:', $items);

        return $items;
    }

    /**
     * Добавя нова cost layer за FIFO
     */
    public static function addCostLayer(int $variantId, int $storageObjectId, float $quantity, float $unitCost): void
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
    public static function getCostFromLayers(int $variantId, int $storageObjectId, float $quantity): float
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
            if ($remainingQty <= 0)
                break;

            $takeQty = min(floatval($layer['remaining']), $remainingQty);
            $totalCost += $takeQty * floatval($layer['unit_cost']);
            $layer['remaining'] -= $takeQty;
            $remainingQty -= $takeQty;
        }

        // Премахване на празните слоеве
        $stock->cost_layers = array_values(array_filter($layers, fn($l) => $l['remaining'] > 0));
        $stock->save();

        return $quantity > 0 ? $totalCost / $quantity : 0;
    }
}