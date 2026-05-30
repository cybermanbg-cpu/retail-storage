<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Size extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Релация към собственика (ако размерът е специфичен за собственик)
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    // Релация към вариантите, които използват този размер
    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Релация към баркодовете, които използват този размер
    public function productBarcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }
}