<?php

namespace App\Http\Controllers;

use App\Models\ActiveCart;
use App\Models\Owner;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Stock;
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
        
        $totalClients = \App\Models\Client::where('owner_id', $ownerId)->count();
        
        $todaySales = Receipt::where('owner_id', $ownerId)
            ->whereDate('created_at', today())
            ->sum('total_amount');
        
        $totalSales = Receipt::where('owner_id', $ownerId)->sum('total_amount');
        
        // ========================================
        // ОБОРОТ ЗА ВЧЕРА (за сравнение)
        // ========================================
        
        $yesterdaySales = Receipt::where('owner_id', $ownerId)
            ->whereDate('created_at', today()->subDay())
            ->sum('total_amount');
        
        $todayGrowth = $yesterdaySales > 0 
            ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 
            : ($todaySales > 0 ? 100 : 0);
        
        // ========================================
        // МЕСЕЧЕН ОБОРОТ
        // ========================================
        
        $monthSales = Receipt::where('owner_id', $ownerId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');
        
        $lastMonthSales = Receipt::where('owner_id', $ownerId)
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('total_amount');
        
        $monthGrowth = $lastMonthSales > 0 
            ? (($monthSales - $lastMonthSales) / $lastMonthSales) * 100 
            : ($monthSales > 0 ? 100 : 0);
        
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
        // АКТИВНИ КАСИЕРИ
        // ========================================
        
        $activeCashiers = User::role('cashier')
            ->where('owner_id', $ownerId)
            ->whereHas('receipts', function ($q) {
                $q->whereMonth('created_at', now()->month)
                    ->where('type', 'sale');
            })
            ->count();
        
        // ========================================
        // ТОП КАСИЕР ЗА МЕСЕЦА
        // ========================================
        
        $topCashierOfMonth = User::role('cashier')
            ->where('owner_id', $ownerId)
            ->withSum(['receipts as monthly_revenue' => function ($q) {
                $q->whereMonth('created_at', now()->month)
                    ->where('type', 'sale');
            }], 'total_amount')
            ->orderByDesc('monthly_revenue')
            ->first();
        
        // ========================================
        // ОБОРОТИ ПО КАСИЕРИ ЗА ДЕНЯ (само за admin/owner)
        // ========================================
        
        $isAdmin = $user->hasRole('super_admin') || $user->hasRole('owner');
        $cashierTurnovers = collect();
        
        if ($isAdmin) {
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
        // ТОП 5 ПРОДУКТА ЗА МЕСЕЦА
        // ========================================
        
        $topProducts = DB::table('receipt_items')
            ->join('receipts', 'receipt_items.receipt_id', '=', 'receipts.id')
            ->join('product_variants', 'receipt_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('receipts.type', 'sale')
            ->where('receipts.owner_id', $ownerId)
            ->whereMonth('receipts.created_at', now()->month)
            ->select(
                'products.name as product_name',
                DB::raw('SUM(receipt_items.quantity) as total_quantity'),
                DB::raw('SUM(receipt_items.total) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
        
        // ========================================
        // ПРОДУКТИ С НИСЪК ЗАПАС
        // ========================================
        
        $lowStockProducts = Stock::whereHas('productVariant.product', function ($q) use ($ownerId) {
                $q->where('owner_id', $ownerId);
            })
            ->whereRaw('quantity <= min_quantity')
            ->where('quantity', '>', 0)
            ->with(['productVariant.product', 'storageObject'])
            ->limit(10)
            ->get();
        
        // ========================================
        // ПОСЛЕДНИ ПРОДАЖБИ
        // ========================================
        
        $recentSales = Receipt::where('owner_id', $ownerId)
            ->where('type', 'sale')
            ->with(['client', 'user', 'storageObject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // ========================================
        // ДРУГИ СТАТИСТИКИ
        // ========================================
        
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
            'totalClients',
            'todaySales',
            'totalSales',
            'todayGrowth',
            'monthSales',
            'monthGrowth',
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
            'isAdmin',
            'activeCashiers',
            'topCashierOfMonth',
            'topProducts',
            'lowStockProducts',
            'recentSales'
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