<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = [
        'product_variant_id',
        'storage_object_id',
        'quantity',
        'average_cost',
        'last_purchase_cost',
        'last_purchase_date',
        'reserved_quantity',
        'min_quantity'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'average_cost' => 'decimal:4',
        'last_purchase_cost' => 'decimal:4',
        'last_purchase_date' => 'datetime',
        'reserved_quantity' => 'decimal:3',
        'min_quantity' => 'decimal:3',
    ];

    protected $attributes = [
        'average_cost' => 0,
        'last_purchase_cost' => 0,
        'reserved_quantity' => 0,
        'min_quantity' => 0,
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function storageObject(): BelongsTo
    {
        return $this->belongsTo(StorageObject::class);
    }

    // Наличност за продажба
    public function getAvailableAttribute(): float
    {
        return $this->quantity - $this->reserved_quantity;
    }

    // Проверка за нисък запас
    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }
    
    // Актуализиране на средната цена (Average Cost метод)
    public function updateAverageCost(float $newQuantity, float $newCost): void
    {
        $oldTotalValue = $this->quantity * $this->average_cost;
        $newTotalValue = $newQuantity * $newCost;
        $newTotalQuantity = $this->quantity + $newQuantity;
        
        if ($newTotalQuantity > 0) {
            $this->average_cost = round(($oldTotalValue + $newTotalValue) / $newTotalQuantity, 4);
        }
        
        $this->last_purchase_cost = $newCost;
        $this->last_purchase_date = now();
        $this->quantity = $newTotalQuantity;
        $this->save();
    }
}