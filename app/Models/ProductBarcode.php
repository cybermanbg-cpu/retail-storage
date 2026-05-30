<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBarcode extends Model
{
    protected $fillable = [
        'product_id',
        'barcode',
        'type',
        'color_id',
        'size_id',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // Релация към продукта
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Релация към цвета (ако баркодът е за конкретен цвят)
    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    // Релация към размера (ако баркодът е за конкретен размер)
    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    // Проверка дали е основният баркод
    public function getIsPrimaryAttribute($value)
    {
        return (bool) $value;
    }
}