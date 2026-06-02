<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Взема owner_id според ролята
     */
    private function getOwnerId()
    {
        $user = Auth::user();

        // Супер администратор вижда всички (owner_id = null)
        if ($user->hasRole('super_admin')) {
            return null;
        }

        // Собственик и мениджър виждат само своите
        return $user->owner_id ?? 1;
    }

    /**
     * Ограничава заявката според ролята
     */
    private function applyOwnerFilter($query)
    {
        $ownerId = $this->getOwnerId();

        if ($ownerId !== null) {
            return $query->where('owner_id', $ownerId);
        }

        return $query;
    }

    /**
     * ОБОРОТИ ПО ПРОДУКТИ
     */
    public function productSales(Request $request)
    {
        $ownerId = $this->getOwnerId();

        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))->endOfDay()
            : now();

        $products = Product::where('type', 'product')
            ->where('is_active', true)
            ->when($ownerId, function ($query) use ($ownerId) {
                return $query->where('owner_id', $ownerId);
            })
            ->get();

        $report = [];

        foreach ($products as $product) {
            $sales = ReceiptItem::whereHas('receipt', function ($q) use ($startDate, $endDate, $ownerId) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                    ->where('type', 'sale');
                if ($ownerId) {
                    $q->where('owner_id', $ownerId);
                }
            })
                ->whereHas('productVariant.product', function ($q) use ($product) {
                    $q->where('id', $product->id);
                })
                ->select(
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(total) as total_revenue')
                )
                ->first();

            $purchases = PurchaseItem::whereHas('purchase', function ($q) use ($startDate, $endDate, $ownerId) {
                $q->whereBetween('purchase_date', [$startDate, $endDate]);
                if ($ownerId) {
                    $q->where('owner_id', $ownerId);
                }
            })
                ->whereHas('productVariant.product', function ($q) use ($product) {
                    $q->where('id', $product->id);
                })
                ->select(
                    DB::raw('SUM(quantity) as total_purchased'),
                    DB::raw('SUM(total_cost) as total_cost')
                )
                ->first();

            $report[] = [
                'product_name' => $product->name,
                'sku' => $product->sku,
                'unit' => $product->unitOfMeasure?->symbol ?? 'бр.',
                'sold_quantity' => $sales->total_quantity ?? 0,
                'revenue' => $sales->total_revenue ?? 0,
                'purchased_quantity' => $purchases->total_purchased ?? 0,
                'cost' => $purchases->total_cost ?? 0,
                'profit' => ($sales->total_revenue ?? 0) - ($purchases->total_cost ?? 0),
                'margin' => $sales->total_revenue > 0
                    ? (($sales->total_revenue - ($purchases->total_cost ?? 0)) / $sales->total_revenue) * 100
                    : 0,
            ];
        }

        $report = collect($report)->sortByDesc('revenue')->values();

        return view('reports.product-sales', compact('report', 'startDate', 'endDate'));
    }

    /**
     * ОБОРОТИ ПО КЛИЕНТИ
     */
    public function clientSales(Request $request)
    {
        $ownerId = $this->getOwnerId();

        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))->endOfDay()
            : now();

        $clients = Client::where('is_active', true)
            ->when($ownerId, function ($query) use ($ownerId) {
                return $query->where('owner_id', $ownerId);
            })
            ->get();

        $report = [];

        foreach ($clients as $client) {
            $sales = Receipt::where('client_id', $client->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'sale')
                ->when($ownerId, function ($query) use ($ownerId) {
                    return $query->where('owner_id', $ownerId);
                })
                ->select(
                    DB::raw('COUNT(*) as receipt_count'),
                    DB::raw('SUM(total_amount) as total_revenue'),
                    DB::raw('SUM(total_vat) as total_vat')
                )
                ->first();

            $report[] = [
                'client_name' => $client->name,
                'client_phone' => $client->phone,
                'receipt_count' => $sales->receipt_count ?? 0,
                'total_revenue' => $sales->total_revenue ?? 0,
                'total_vat' => $sales->total_vat ?? 0,
                'avg_receipt' => ($sales->receipt_count ?? 0) > 0
                    ? ($sales->total_revenue / $sales->receipt_count)
                    : 0,
            ];
        }

        $report = collect($report)->sortByDesc('total_revenue')->values();

        return view('reports.client-sales', compact('report', 'startDate', 'endDate'));
    }

    /**
     * ОБОРОТИ ПО КАСИЕРИ (само за super_admin и owner)
     */
    public function cashierSales(Request $request)
    {
        $user = Auth::user();

        // Касиерите нямат достъп (вече е проверено в конструктора)
        // Само super_admin и owner виждат този отчет
        if (!$user->hasRole('super_admin') && !$user->hasRole('owner')) {
            abort(403, 'Нямате достъп до този отчет.');
        }

        $ownerId = $this->getOwnerId();

        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))->endOfDay()
            : now();

        $cashiers = User::role('cashier')
            ->when($ownerId, function ($query) use ($ownerId) {
                return $query->where('owner_id', $ownerId);
            })
            ->get();

        $report = [];

        foreach ($cashiers as $cashier) {
            $sales = Receipt::where('user_id', $cashier->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'sale')
                ->when($ownerId, function ($query) use ($ownerId) {
                    return $query->where('owner_id', $ownerId);
                })
                ->select(
                    DB::raw('COUNT(*) as receipt_count'),
                    DB::raw('SUM(total_amount) as total_revenue'),
                    DB::raw('SUM(total_vat) as total_vat'),
                    DB::raw('COUNT(DISTINCT client_id) as unique_clients')
                )
                ->first();

            $report[] = [
                'cashier_name' => $cashier->name,
                'receipt_count' => $sales->receipt_count ?? 0,
                'total_revenue' => $sales->total_revenue ?? 0,
                'total_vat' => $sales->total_vat ?? 0,
                'unique_clients' => $sales->unique_clients ?? 0,
                'avg_receipt' => ($sales->receipt_count ?? 0) > 0
                    ? ($sales->total_revenue / $sales->receipt_count)
                    : 0,
            ];
        }

        $report = collect($report)->sortByDesc('total_revenue')->values();

        return view('reports.cashier-sales', compact('report', 'startDate', 'endDate'));
    }

    /**
     * ОБОРОТИ ПО МЕСЕЦИ
     */
    public function monthlySales(Request $request)
    {
        $ownerId = $this->getOwnerId();
        $year = $request->get('year', now()->year);

        $sales = Receipt::whereYear('created_at', $year)
            ->where('type', 'sale')
            ->when($ownerId, function ($query) use ($ownerId) {
                return $query->where('owner_id', $ownerId);
            })
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as receipt_count'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(total_vat) as total_vat')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $purchases = Purchase::whereYear('purchase_date', $year)
            ->when($ownerId, function ($query) use ($ownerId) {
                return $query->where('owner_id', $ownerId);
            })
            ->select(
                DB::raw('MONTH(purchase_date) as month'),
                DB::raw('SUM(total) as total_cost')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $report = [];
        $months = ['Яну', 'Фев', 'Мар', 'Апр', 'Май', 'Юни', 'Юли', 'Авг', 'Сеп', 'Окт', 'Ное', 'Дек'];

        foreach ($months as $i => $monthName) {
            $monthNum = $i + 1;
            $sale = $sales->firstWhere('month', $monthNum);
            $purchase = $purchases->firstWhere('month', $monthNum);

            $revenue = $sale ? $sale->total_revenue : 0;
            $cost = $purchase ? $purchase->total_cost : 0;

            $report[] = [
                'month' => $monthName,
                'receipt_count' => $sale ? $sale->receipt_count : 0,
                'revenue' => $revenue,
                'vat' => $sale ? $sale->total_vat : 0,
                'cost' => $cost,
                'profit' => $revenue - $cost,
                'margin' => $revenue > 0 ? (($revenue - $cost) / $revenue) * 100 : 0,
            ];
        }

        return view('reports.monthly-sales', compact('report', 'year'));
    }

    /**
     * ДОСТАВКИ ПО ПРОДУКТИ
     */
    public function productPurchases(Request $request)
    {
        $ownerId = $this->getOwnerId();

        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))->endOfDay()
            : now();

        $products = Product::where('type', 'product')
            ->where('is_active', true)
            ->when($ownerId, function ($query) use ($ownerId) {
                return $query->where('owner_id', $ownerId);
            })
            ->get();

        $report = [];

        foreach ($products as $product) {
            $purchases = PurchaseItem::whereHas('purchase', function ($q) use ($startDate, $endDate, $ownerId) {
                $q->whereBetween('purchase_date', [$startDate, $endDate]);
                if ($ownerId) {
                    $q->where('owner_id', $ownerId);
                }
            })
                ->whereHas('productVariant.product', function ($q) use ($product) {
                    $q->where('id', $product->id);
                })
                ->select(
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(total_cost) as total_cost'),
                    DB::raw('AVG(final_unit_cost) as avg_cost')
                )
                ->first();

            $report[] = [
                'product_name' => $product->name,
                'sku' => $product->sku,
                'unit' => $product->unitOfMeasure?->symbol ?? 'бр.',
                'purchased_quantity' => $purchases->total_quantity ?? 0,
                'total_cost' => $purchases->total_cost ?? 0,
                'avg_cost' => $purchases->avg_cost ?? 0,
            ];
        }

        $report = collect($report)->filter(fn($item) => $item['purchased_quantity'] > 0)->values();

        return view('reports.product-purchases', compact('report', 'startDate', 'endDate'));
    }

    /**
     * ДЪШБОРД С ОБОБЩЕНИ СТАТИСТИКИ
     */
    public function dashboard()
    {
        $ownerId = $this->getOwnerId();

        // Общи статистики
        $totalProducts = Product::where('type', 'product')
            ->when($ownerId, fn($q) => $q->where('owner_id', $ownerId))
            ->count();

        $totalClients = Client::when($ownerId, fn($q) => $q->where('owner_id', $ownerId))
            ->count();

        $totalCashiers = User::role('cashier')
            ->when($ownerId, fn($q) => $q->where('owner_id', $ownerId))
            ->count();

        // Продажби днес
        $todaySales = Receipt::where('type', 'sale')
            ->whereDate('created_at', today())
            ->when($ownerId, fn($q) => $q->where('owner_id', $ownerId))
            ->sum('total_amount');

        $todayReceipts = Receipt::where('type', 'sale')
            ->whereDate('created_at', today())
            ->when($ownerId, fn($q) => $q->where('owner_id', $ownerId))
            ->count();

        // Продажби този месец
        $monthSales = Receipt::where('type', 'sale')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->when($ownerId, fn($q) => $q->where('owner_id', $ownerId))
            ->sum('total_amount');

        // Топ продукт за месеца
        $topProduct = ReceiptItem::select('product_name_snapshot', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('receipt', function ($q) use ($ownerId) {
                $q->where('type', 'sale')
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month);
                if ($ownerId) {
                    $q->where('owner_id', $ownerId);
                }
            })
            ->groupBy('product_name_snapshot')
            ->orderByDesc('total_qty')
            ->first();

        // Топ клиент за месеца
        $topClient = Receipt::select('client_id', DB::raw('SUM(total_amount) as total'))
            ->where('type', 'sale')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereNotNull('client_id')
            ->when($ownerId, fn($q) => $q->where('owner_id', $ownerId))
            ->groupBy('client_id')
            ->orderByDesc('total')
            ->with('client')
            ->first();

        return view('reports.dashboard', compact(
            'totalProducts',
            'totalClients',
            'totalCashiers',
            'todaySales',
            'todayReceipts',
            'monthSales',
            'topProduct',
            'topClient'
        ));
    }
}