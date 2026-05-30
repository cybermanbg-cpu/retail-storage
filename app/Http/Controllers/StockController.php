<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StorageObject;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Показва всички наличности
     */
    public function index()
    {
        $stocks = Stock::with(['productVariant.product', 'storageObject'])
            ->orderBy('quantity', 'asc')
            ->paginate(20);
            
        $products = Product::where('type', 'product')->get();
        $storageObjects = StorageObject::where('is_active', true)->get();
        
        return view('stocks.index', compact('stocks', 'products', 'storageObjects'));
    }
    
    /**
     * Коригиране на наличност
     */
    public function adjust(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'quantity' => 'required|integer|min:0',
        ]);
        
        $stock = Stock::findOrFail($request->stock_id);
        $stock->update(['quantity' => $request->quantity]);
        
        return redirect()->route('stocks.index')
            ->with('success', 'Наличността е актуализирана успешно!');
    }
    
    /**
     * Проверка на наличност за конкретен вариант и обект (AJAX)
     */
    public function check(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'storage_object_id' => 'required|exists:storage_objects,id',
        ]);
        
        $stock = Stock::where('product_variant_id', $request->product_variant_id)
            ->where('storage_object_id', $request->storage_object_id)
            ->first();
            
        return response()->json([
            'quantity' => $stock ? $stock->quantity : 0,
            'available' => $stock ? $stock->available : 0,
            'min_quantity' => $stock ? $stock->min_quantity : 0,
            'is_low_stock' => $stock ? $stock->is_low_stock : false,
        ]);
    }
    
    /**
     * Продукти с нисък запас
     */
    public function lowStock()
    {
        $lowStocks = Stock::with(['productVariant.product', 'storageObject'])
            ->whereRaw('quantity <= min_quantity')
            ->where('quantity', '>', 0)
            ->orderBy('quantity', 'asc')
            ->get();
            
        return view('stocks.low', compact('lowStocks'));
    }
}