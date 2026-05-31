<?php

namespace App\Http\Controllers;

use App\Models\ActiveCart;
use App\Models\Owner;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        
        // ========================================
        // СТАТИСТИКИ ЗА СОБСТВЕНИКА
        // ========================================
        
        $totalProducts = Product::where('owner_id', $ownerId)
            ->where('type', 'product')
            ->count();
        
        $todaySales = Receipt::where('owner_id', $ownerId)
            ->whereDate('created_at', today())
            ->sum('total_amount');
        
        $totalSales = Receipt::where('owner_id', $ownerId)->sum('total_amount');
        
        // ========================================
        // ПЕРСОНАЛЕН ОБОРОТ НА ЛОГНАТИЯ КАСИЕР
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
        
        $myTotalTurnover = Receipt::where('owner_id', $ownerId)
            ->where('user_id', $user->id)
            ->sum('total_amount');
        
        $myTodaySalesCount = Receipt::where('owner_id', $ownerId)
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();
        
        // ========================================
        // ОБОРОТИ ПО КАСИЕРИ ЗА ДЕНЯ (само за admin/owner)
        // ========================================
        
        $isAdmin = $user->hasRole('super_admin') || $user->hasRole('owner');
        $cashierTurnovers = collect();
        
        if ($isAdmin) {
            // Обороти по касиери за днешния ден
            $cashierTurnovers = User::where('owner_id', $ownerId)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'cashier');
                })
                ->withCount(['receipts as today_sales_count' => function ($q) {
                    $q->whereDate('created_at', today());
                }])
                ->withSum(['receipts as today_turnover' => function ($q) {
                    $q->whereDate('created_at', today());
                }], 'total_amount')
                ->orderByDesc('today_turnover')
                ->get()
                ->map(function ($cashier) {
                    return [
                        'id' => $cashier->id,
                        'name' => $cashier->name,
                        'today_sales_count' => $cashier->today_sales_count,
                        'today_turnover' => $cashier->today_turnover ?? 0,
                    ];
                });
        }
        
        // ========================================
        // ТОП 5 КАСИЕРИ ЗА МЕСЕЦА (само за admin/owner)
        // ========================================
        
        $topCashiers = collect();
        
        if ($isAdmin) {
            $topCashiers = User::where('owner_id', $ownerId)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'cashier');
                })
                ->withSum(['receipts as monthly_turnover' => function ($q) {
                    $q->whereMonth('created_at', now()->month);
                }], 'total_amount')
                ->orderByDesc('monthly_turnover')
                ->limit(5)
                ->get()
                ->map(function ($cashier) {
                    return [
                        'id' => $cashier->id,
                        'name' => $cashier->name,
                        'monthly_turnover' => $cashier->monthly_turnover ?? 0,
                    ];
                });
        }
        
        // ========================================
        // ДРУГИ СТАТИСТИКИ
        // ========================================
        
        // Проверка дали потребителят има роля
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
            'activeCarts',
            'myTodayTurnover',
            'myWeekTurnover',
            'myMonthTurnover',
            'myTotalTurnover',
            'myTodaySalesCount',
            'cashierTurnovers',
            'topCashiers',
            'isAdmin'
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