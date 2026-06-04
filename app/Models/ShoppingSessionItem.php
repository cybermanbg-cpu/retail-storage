<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingSessionItem extends Model
{
    protected $fillable = [
        'shopping_session_id',
        'kiosk_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
        'unit',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected static function booted()
    {
        // При създаване или обновяване на артикул
        static::saved(function ($item) {
            if ($item->shopping_session_id) {
                $item->shoppingSession?->recalculateTotal();
            }
        });

        // При изтриване на артикул
        static::deleted(function ($item) {
            if ($item->shopping_session_id) {
                $item->shoppingSession?->recalculateTotal();
            }
        });
    }

    public function shoppingSession(): BelongsTo
    {
        return $this->belongsTo(ShoppingSession::class);
    }

    public function kiosk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kiosk_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}