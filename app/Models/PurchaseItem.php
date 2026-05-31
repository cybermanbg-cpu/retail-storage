<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'delivery_cost_share',
        'final_unit_cost',
        'unit_of_measure_snapshot',
        'decimal_places_snapshot'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',           // ⭐ Променено от 'integer' на 'decimal:3'
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'delivery_cost_share' => 'decimal:4',
        'final_unit_cost' => 'decimal:4',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}