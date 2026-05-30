<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    protected $fillable = [
        'name', 'company_name', 'email', 'phone', 'vat_number', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function storageObjects(): HasMany
    {
        return $this->hasMany(StorageObject::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}