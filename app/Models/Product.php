<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'sku',
        'description',
        'type',
        'base_price',
        'cost',
        'vat_rate',
        'has_variants',
        'is_active',
        'unit_of_measure_id',
    ];

    protected $casts = [
        'has_variants' => 'boolean',
        'is_active' => 'boolean',
        'base_price' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    public function getQuantityWithUnitAttribute()
    {
        if ($this->unitOfMeasure) {
            return number_format(0, $this->unitOfMeasure->decimal_places) . ' ' . $this->unitOfMeasure->symbol;
        }
        return '0 бр.';
    }

    /**
     * Аксесор за символ на мерната единица
     */
    public function getUnitSymbolAttribute(): string
    {
        return $this->unitOfMeasure?->symbol ?? 'бр.';
    }

    /**
     * Аксесор за десетични знаци на мерната единица
     */
    public function getUnitDecimalPlacesAttribute(): int
    {
        return $this->unitOfMeasure?->decimal_places ?? 0;
    }
}