<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveCart extends Model
{
    protected $table = 'active_carts';
    
    protected $fillable = [
        'user_id',
        'owner_id',
        'cashier_id',
        'session_id',
        'cart_name',
        'client_id',
        'storage_object_id',
        'items',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function storageObject(): BelongsTo
    {
        return $this->belongsTo(StorageObject::class);
    }
    
    // Scope за текущия собственик
    public function scopeForOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }
    
    // Scope за текущия касиер
    public function scopeForCashier($query, $cashierId)
    {
        return $query->where('cashier_id', $cashierId);
    }
    
    // Scope за активни колички
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}