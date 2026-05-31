<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptItem extends Model
{
    protected $fillable = [
        'receipt_id',
        'product_variant_id',
        'product_name_snapshot',
        'sku_snapshot',
        'unit_of_measure_snapshot',
        'decimal_places_snapshot',
        'color_name',
        'size_name',
        'quantity',
        'unit_price',
        'vat_rate',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'delivery_cost_share' => 'decimal:2',
        'final_unit_cost' => 'decimal:2',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}