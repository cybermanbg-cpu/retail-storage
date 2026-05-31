<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductVariant;

class ProductObserver
{
    public function created(Product $product): void
    {
        // Ако продуктът няма варианти, създай един празен вариант
        if ($product->type === 'product' && $product->variants()->count() === 0) {
            ProductVariant::create([
                'product_id' => $product->id,
                'color_id' => null,
                'size_id' => null,
                'sku_suffix' => null,
                'price_adjustment' => 0,
                'is_active' => true,
            ]);
        }
    }
}