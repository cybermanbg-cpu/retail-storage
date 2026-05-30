<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = [
        'product_variant_id', 'storage_object_id', 'quantity',
        'reserved_quantity', 'min_quantity'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'min_quantity' => 'integer',
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
    public function getAvailableAttribute(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }

    // Проверка за нисък запас
    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }
}