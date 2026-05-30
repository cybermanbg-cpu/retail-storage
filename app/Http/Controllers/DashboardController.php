<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Receipt;
use App\Models\Stock;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::where('type', 'product')->count();
        $totalServices = Product::where('type', 'service')->count();
        $totalSales = Receipt::where('type', 'sale')->count();
        $totalRevenue = Receipt::where('type', 'sale')->sum('total_amount');
        
        $lowStock = Stock::with(['productVariant.product'])
            ->whereRaw('quantity <= min_quantity')
            ->where('quantity', '>', 0)
            ->get();
        
        return view('dashboard', compact(
            'totalProducts', 'totalServices', 'totalSales', 'totalRevenue', 'lowStock'
        ));
    }
}