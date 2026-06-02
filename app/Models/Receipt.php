<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Receipt extends Model
{
    protected $fillable = [
        'owner_id',
        'storage_object_id',
        'client_id',
        'user_id',
        'receipt_number',
        'type',
        'total_amount',
        'total_vat',
        'payment_method',
        'amount_paid',
        'change_amount',
        'notes',
        'is_invoiced'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_vat' => 'decimal:2',
        'is_invoiced' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function storageObject(): BelongsTo
    {
        return $this->belongsTo(StorageObject::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceiptItem::class);
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_receipt');
    }

    public function getGrandTotalAttribute(): float
    {
        return $this->total_amount + $this->total_vat;
    }
}