<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'owner_id', 
        'name', 
        'slug', 
        'icon', 
        'color', 
        'sort_order', 
        'default_discount', 
        'is_active',
        'show_in_restaurant_pos'  // ⭐ ДОБАВЕНО
    ];

    protected $casts = [
        'default_discount' => 'decimal:2',
        'is_active' => 'boolean',
        'show_in_restaurant_pos' => 'boolean',  // ⭐ ДОБАВЕНО
        'sort_order' => 'integer',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product')
            ->withTimestamps();
    }
    
    // Взема активните продукти в категорията
    public function activeProducts()
    {
        return $this->products()->where('is_active', true);
    }
}