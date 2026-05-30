<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['owner', 'variants.color', 'variants.size'])->paginate(20);
        return view('products.index', compact('products'));
    }
    
    public function create()
    {
        $colors = Color::where('is_active', true)->get();
        $sizes = Size::where('is_active', true)->get();
        return view('products.create', compact('colors', 'sizes'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
            'type' => 'required|in:product,service',
            'base_price' => 'required|numeric|min:0',
            'has_variants' => 'boolean',
            'colors' => 'array|required_if:has_variants,true',
            'sizes' => 'array|required_if:has_variants,true',
        ]);
        
        $product = Product::create([
            'owner_id' => 1, // временно
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'type' => $validated['type'],
            'base_price' => $validated['base_price'],
            'has_variants' => $validated['has_variants'] ?? false,
            'is_active' => true,
        ]);
        
        // Създаване на варианти
        if ($product->has_variants && isset($validated['colors']) && isset($validated['sizes'])) {
            foreach ($validated['colors'] as $colorId) {
                foreach ($validated['sizes'] as $sizeId) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'color_id' => $colorId,
                        'size_id' => $sizeId,
                        'is_active' => true,
                    ]);
                }
            }
        }
        
        return redirect()->route('products.index')->with('success', 'Продуктът е създаден успешно!');
    }
    
    public function edit(Product $product)
    {
        $colors = Color::where('is_active', true)->get();
        $sizes = Size::where('is_active', true)->get();
        return view('products.edit', compact('product', 'colors', 'sizes'));
    }
    
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
        ]);
        
        $product->update($validated);
        
        return redirect()->route('products.index')->with('success', 'Продуктът е обновен успешно!');
    }
    
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Продуктът е изтрит!');
    }
}