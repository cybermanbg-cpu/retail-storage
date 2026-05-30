<?php

namespace App\Models;

use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'owner_id', 'storage_object_id', 'supplier_id', 'user_id',
        'purchase_number', 'purchase_date', 'invoice_date', 'supplier_invoice',
        'subtotal', 'discount', 'delivery_cost', 'vat', 'total', 'status', 'notes'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'vat' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function storageObject(): BelongsTo
    {
        return $this->belongsTo(StorageObject::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'supplier_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}