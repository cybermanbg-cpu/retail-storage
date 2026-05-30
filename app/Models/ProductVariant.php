<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'sku_suffix',
        'price_adjustment',
        'external_id',
        'is_active',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = ['full_sku', 'final_price', 'display_name'];


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(ReceiptItem::class);
    }

    public function getFullSkuAttribute(): string
    {
        return $this->product->sku . ($this->sku_suffix ? '-' . $this->sku_suffix : '');
    }

    public function getFinalPriceAttribute(): float
    {
        return $this->product->base_price + $this->price_adjustment;
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->product->name;
        if ($this->color) {
            $name .= ' - ' . $this->color->name;
        }
        if ($this->size) {
            $name .= ' / ' . $this->size->name;
        }
        return $name;
    }
}