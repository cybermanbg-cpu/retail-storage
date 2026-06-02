<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Invoice extends Model
{
    protected $fillable = [
        'owner_id',
        'client_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'subtotal',
        'discount',
        'vat',
        'total',
        'status',
        'notes'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'vat' => 'decimal:2',
        'total' => 'decimal:2',
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

    /**
     * Генерира уникален номер на фактура за собственика
     */
    public static function generateInvoiceNumber(int $ownerId): string
    {
        $owner = Owner::find($ownerId);

        if (!$owner) {
            return 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
        }

        $year = date('Y');
        $month = date('m');

        $lastInvoice = self::where('owner_id', $ownerId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            preg_match('/-(\d+)$/', $lastInvoice->invoice_number, $matches);
            $lastNumber = intval($matches[1] ?? 0);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'INV-' . $year . $month . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}