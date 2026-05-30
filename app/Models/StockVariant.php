<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockVariant extends Model
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

    public function getAvailableQuantityAttribute(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }
}