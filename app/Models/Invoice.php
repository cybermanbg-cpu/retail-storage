<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Invoice extends Model
{
    protected $fillable = [
        'owner_id', 'client_id', 'invoice_number', 'issue_date',
        'due_date', 'total_amount', 'total_vat', 'status', 'notes'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'total_vat' => 'decimal:2',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function receipts(): BelongsToMany
    {
        return $this->belongsToMany(Receipt::class, 'invoice_receipt');
    }
}