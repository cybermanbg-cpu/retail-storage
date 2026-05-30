<?php

namespace App\Http\Controllers;

use App\Models\ActiveCart;
use App\Models\Owner;
use App\Models\Product;
use App\Models\Receipt;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // Ако няма логнат потребител, покажи само публична информация
        if (!Auth::check()) {
            $totalProducts = Product::where('type', 'product')->count();
            $latestProducts = Product::with(['variants.color', 'variants.size'])
                ->latest()
                ->take(6)
                ->get();
            
            return view('home', compact('totalProducts', 'latestProducts'));
        }
        
        // Вземане на текущия собственик (от user->owner_id или от сесия)
        $ownerId = $this->getCurrentOwnerId();
        $user = Auth::user();
        
        // Статистики само за текущия собственик
        $totalProducts = Product::where('owner_id', $ownerId)
            ->where('type', 'product')
            ->count();
        
        $todaySales = Receipt::where('owner_id', $ownerId)
            ->whereDate('created_at', today())
            ->sum('total_amount');
        
        $totalSales = Receipt::where('owner_id', $ownerId)->sum('total_amount');
        
        // Проверка дали потребителят има роля (с безопасно извикване)
        $isAdmin = $user->hasRole('super_admin') || $user->hasRole('owner');
        
        // Незавършени продажби за текущия обект/касиер
        $incompleteSales = ActiveCart::where('owner_id', $ownerId)
            ->where('status', 'active')
            ->when(!$isAdmin, function ($query) use ($user) {
                return $query->where('cashier_id', $user->id);
            })
            ->count();
        
        $latestProducts = Product::with(['variants.color', 'variants.size'])
            ->where('owner_id', $ownerId)
            ->latest()
            ->take(6)
            ->get();
        
        // Активни колички за текущия обект (ако има избран)
        $activeCarts = ActiveCart::where('owner_id', $ownerId)
            ->where('status', 'active')
            ->with('cashier', 'storageObject')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
        
        return view('home', compact(
            'totalProducts', 
            'todaySales', 
            'totalSales', 
            'incompleteSales',
            'latestProducts',
            'activeCarts'
        ));
    }
    
    /**
     * Взема текущия собственик на логнатия потребител
     */
    private function getCurrentOwnerId(): int
    {
        $user = Auth::user();
        
        // Ако няма логнат потребител
        if (!$user) {
            $owner = Owner::first();
            return $owner?->id ?? 1;
        }
        
        // За супер администратор (няма owner_id)
        if ($user->hasRole('super_admin')) {
            $owner = Owner::first();
            return $owner?->id ?? 1;
        }
        
        // Ако user-а има owner_id директно
        if ($user->owner_id) {
            return $user->owner_id;
        }
        
        // Иначе взимаме от сесията
        if (session()->has('current_owner_id')) {
            return session()->get('current_owner_id');
        }
        
        // Fallback
        $owner = Owner::first();
        return $owner?->id ?? 1;
    }
}