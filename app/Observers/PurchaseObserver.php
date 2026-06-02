<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Models\Stock;

class PurchaseObserver
{
    /**
     * Handle the Purchase "creating" event.
     */
    public function creating(Purchase $purchase): void
    {
        // Ако вече има изчислени стойности, не ги презаписваме
        if (!$purchase->subtotal) {
            $this->calculateTotals($purchase);
        }
    }
    
    /**
     * Handle the Purchase "updating" event.
     */
    public function updating(Purchase $purchase): void
    {
        // Само ако не е завършена и няма изчислени стойности
        if ($purchase->getOriginal('status') !== 'completed' && !$purchase->subtotal) {
            $this->calculateTotals($purchase);
        }
    }
    
    /**
     * Handle the Purchase "created" event.
     */
    public function created(Purchase $purchase): void
    {
        if ($purchase->status === 'completed') {
            $this->updateStock($purchase);
        }
    }
    
    /**
     * Handle the Purchase "updated" event.
     */
    public function updated(Purchase $purchase): void
    {
        // Ако статуса е променен на completed
        if ($purchase->status === 'completed' && $purchase->getOriginal('status') !== 'completed') {
            $this->updateStock($purchase);
        }
    }
    
    /**
     * Изчислява всички суми
     */
    protected function calculateTotals(Purchase $purchase): void
    {
        $subtotal = 0;
        $items = $purchase->items ?? [];
        
        foreach ($items as $item) {
            $quantity = floatval($item['quantity'] ?? 0);
            $unitCost = floatval($item['unit_cost'] ?? 0);
            $totalCost = $quantity * $unitCost;
            $subtotal += $totalCost;
        }
        
        $discount = floatval($purchase->discount ?? 0);
        $deliveryCost = floatval($purchase->delivery_cost ?? 0);
        $vat = floatval($purchase->vat ?? 0);
        
        $discountAmount = $subtotal * ($discount / 100);
        $totalBeforeVat = $subtotal - $discountAmount + $deliveryCost;
        
        $vatAmount = $totalBeforeVat * ($vat / 100);
        $total = $totalBeforeVat + $vatAmount;
        
        $purchase->subtotal = round($subtotal, 2);
        $purchase->total = round($total, 2);
    }
    
    /**
     * Актуализира склада
     */
    protected function updateStock(Purchase $purchase): void
    {
        $purchase->load('items');
        
        foreach ($purchase->items as $item) {
            if (!$item->product_variant_id) continue;
            
            $costForStock = $item->final_unit_cost ?? $item->unit_cost;
            
            $stock = Stock::where('product_variant_id', $item->product_variant_id)
                ->where('storage_object_id', $purchase->storage_object_id)
                ->first();
            
            if ($stock) {
                $oldTotalValue = $stock->quantity * ($stock->average_cost ?? 0);
                $newTotalValue = $item->quantity * $costForStock;
                $newQuantity = $stock->quantity + $item->quantity;
                $newAverageCost = ($oldTotalValue + $newTotalValue) / $newQuantity;
                
                $stock->quantity = $newQuantity;
                $stock->average_cost = round($newAverageCost, 4);
                $stock->last_purchase_cost = $costForStock;
                $stock->last_purchase_date = now();
                $stock->save();
            } else {
                Stock::create([
                    'product_variant_id' => $item->product_variant_id,
                    'storage_object_id' => $purchase->storage_object_id,
                    'quantity' => $item->quantity,
                    'average_cost' => $costForStock,
                    'last_purchase_cost' => $costForStock,
                    'last_purchase_date' => now(),
                    'reserved_quantity' => 0,
                    'min_quantity' => 0,
                ]);
            }
        }
    }
}