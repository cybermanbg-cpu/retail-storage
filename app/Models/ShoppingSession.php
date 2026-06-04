<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoppingSession extends Model
{
    protected $fillable = [
        'session_token',
        'customer_name',
        'customer_phone',
        'note',
        'status',
        'owner_id',
        'created_by',
        'paid_by',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'paid_at',
    ];

    protected $casts = [
        'status' => 'string',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShoppingSessionItem::class);
    }

    /**
     * Преизчислява общата сума на базата на артикулите
     */
    public function recalculateTotal(): float
    {
        $total = $this->items()->sum('total_price');
        $this->total_amount = $total;
        $this->saveQuietly(); // Запазва без да задейства събития
        return $total;
    }
}