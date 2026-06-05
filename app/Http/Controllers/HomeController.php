<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // Ако няма логнат потребител, покажи само публична информация
        if (!Auth::check()) {
            // $totalProducts = Product::where('type', 'product')->count();
            // $latestProducts = Product::with(['variants.color', 'variants.size'])
                // ->latest()
                // ->take(6)
                // ->get();
            
            return view('home');
        }
        
        $user = Auth::user();
        $ownerId = $this->getCurrentOwnerId();
        
        // ========================================
        // ОБЩИ СТАТИСТИКИ
        // ========================================
        
        // Продажби днес
        $todaySales = Receipt::where('owner_id', $ownerId)
            ->whereDate('created_at', today())
            ->sum('total_amount');
        
        // Продажби за месеца
        $monthSales = Receipt::where('owner_id', $ownerId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');
        
        // ========================================
        // МОЯТ ОБОРОТ (за логнатия потребител)
        // ========================================
        
        $myTodayTurnover = Receipt::where('owner_id', $ownerId)
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->sum('total_amount');
        
        $myWeekTurnover = Receipt::where('owner_id', $ownerId)
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('total_amount');
        
        $myMonthTurnover = Receipt::where('owner_id', $ownerId)
            ->where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');
        
        // ========================================
        // ДНЕВЕН ОБОРОТ ПО ПОТРЕБИТЕЛИ (не по касиери)
        // ========================================
        
        $dailyUserTurnovers = User::where('owner_id', $ownerId)
            ->whereHas('receipts', function ($q) {
                $q->whereDate('created_at', today());
            })
            ->withSum(['receipts as today_turnover' => function ($q) {
                $q->whereDate('created_at', today());
            }], 'total_amount')
            ->withCount(['receipts as today_sales_count' => function ($q) {
                $q->whereDate('created_at', today());
            }])
            ->orderByDesc('today_turnover')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'today_sales_count' => $user->today_sales_count ?? 0,
                    'today_turnover' => $user->today_turnover ?? 0,
                ];
            });
        
        // ========================================
        // ДРУГИ ДАННИ ЗА ИЗГЛЕДА
        // ========================================
        
        $latestProducts = Product::with(['variants.color', 'variants.size'])
            ->where('owner_id', $ownerId)
            ->latest()
            ->take(6)
            ->get();
        
        $totalProducts = Product::where('owner_id', $ownerId)
            ->where('type', 'product')
            ->count();
        
        return view('home', compact(
            'totalProducts',
            'todaySales',
            'monthSales',
            'myTodayTurnover',
            'myWeekTurnover',
            'myMonthTurnover',
            'dailyUserTurnovers',
            'latestProducts'
        ));
    }
    
    /**
     * Взема текущия собственик на логнатия потребител
     */
    private function getCurrentOwnerId(): int
    {
        $user = Auth::user();
        
        if (!$user) {
            $owner = Owner::first();
            return $owner?->id ?? 1;
        }
        
        if ($user->hasRole('super_admin')) {
            $owner = Owner::first();
            return $owner?->id ?? 1;
        }
        
        if ($user->owner_id) {
            return $user->owner_id;
        }
        
        if (session()->has('current_owner_id')) {
            return session()->get('current_owner_id');
        }
        
        $owner = Owner::first();
        return $owner?->id ?? 1;
    }
}