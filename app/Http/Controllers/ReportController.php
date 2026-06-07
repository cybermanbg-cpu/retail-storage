<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Receipt;
use App\Models\ShoppingSession;
use App\Models\ShoppingSessionItem;
use App\Models\ReceiptItem;
use App\Models\Stock;
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
     * ОБОРОТИ ПО СЛУЖИТЕЛИ (касиери, мениджъри, собственици)
     */
    public function staffSales(Request $request)
    {
        $user = Auth::user();

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

        // Всички потребители, които имат продажби (касиери, мениджъри, собственици)
        $staff = User::whereHas('receipts', function ($q) use ($startDate, $endDate, $ownerId) {
            $q->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'sale');
            if ($ownerId) {
                $q->where('owner_id', $ownerId);
            }
        })
            ->when($ownerId, function ($query) use ($ownerId) {
                return $query->where('owner_id', $ownerId);
            })
            ->with('roles')
            ->get();

        $report = [];

        foreach ($staff as $staffMember) {
            $sales = Receipt::where('user_id', $staffMember->id)
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

            // Определяне на ролята
            $roleName = 'Касиер';
            if ($staffMember->hasRole('super_admin')) {
                $roleName = 'Супер администратор';
            } elseif ($staffMember->hasRole('owner')) {
                $roleName = 'Собственик';
            } elseif ($staffMember->hasRole('manager')) {
                $roleName = 'Мениджър';
            } elseif ($staffMember->hasRole('cashier')) {
                $roleName = 'Касиер';
            }

            $report[] = [
                'staff_name' => $staffMember->name,
                'role' => $roleName,
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

        return view('reports.staff-sales', compact('report', 'startDate', 'endDate'));
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
     * ПЕЧАЛБА ОТ ПРОДАЖБИТЕ (COGS)
     */
    public function profitAnalysis(Request $request)
    {
        $ownerId = $this->getOwnerId();

        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))->endOfDay()
            : now();

        // Вземаме всички продадени артикули
        $salesItems = ReceiptItem::whereHas('receipt', function ($q) use ($startDate, $endDate, $ownerId) {
            $q->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'sale');
            if ($ownerId) {
                $q->where('owner_id', $ownerId);
            }
        })
            ->with(['productVariant.product', 'receipt'])
            ->get();

        $report = [];
        $totalRevenue = 0;
        $totalCOGS = 0;
        $totalProfit = 0;

        foreach ($salesItems as $item) {
            $product = $item->productVariant->product;
            $quantity = $item->quantity;
            $sellingPrice = $item->unit_price;
            $revenue = $item->total;

            // Изчисляване на себестойността (средна цена от наличностите)
            $costPerUnit = $this->getCostPerUnit($product->id, $item->receipt->storage_object_id, $item->created_at);
            $cogs = $quantity * $costPerUnit;
            $profit = $revenue - $cogs;

            $totalRevenue += $revenue;
            $totalCOGS += $cogs;
            $totalProfit += $profit;

            $report[] = [
                'receipt_number' => $item->receipt->receipt_number,
                'date' => $item->receipt->created_at,
                'product_name' => $product->name,
                'variant' => $this->getVariantName($item->productVariant),
                'quantity' => $quantity,
                'unit' => $product->unitOfMeasure?->symbol ?? 'бр.',
                'selling_price' => $sellingPrice,
                'revenue' => $revenue,
                'cost_per_unit' => $costPerUnit,
                'cogs' => $cogs,
                'profit' => $profit,
                'margin_percent' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
            ];
        }

        // Сортиране по печалба низходящо
        $report = collect($report)->sortByDesc('profit')->values();

        // Агрегиране по продукти
        $productSummary = collect($report)->groupBy('product_name')->map(function ($items, $productName) {
            return [
                'product_name' => $productName,
                'quantity' => $items->sum('quantity'),
                'revenue' => $items->sum('revenue'),
                'cogs' => $items->sum('cogs'),
                'profit' => $items->sum('profit'),
                'margin_percent' => $items->sum('revenue') > 0
                    ? ($items->sum('profit') / $items->sum('revenue')) * 100
                    : 0,
            ];
        })->sortByDesc('profit')->values();

        return view('reports.profit-analysis', compact('report', 'productSummary', 'startDate', 'endDate', 'totalRevenue', 'totalCOGS', 'totalProfit'));
    }

    /**
     * Взема себестойност на продукт към определена дата
     */
    private function getCostPerUnit($productId, $storageObjectId, $date)
    {
        $result = DB::table('purchase_items')
            ->select('purchase_items.final_unit_cost')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('product_variants', 'purchase_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('products.id', $productId)
            ->where('purchases.storage_object_id', $storageObjectId)
            ->where('purchases.purchase_date', '<=', $date)
            ->orderBy('purchases.purchase_date', 'desc')
            ->first();

        if ($result) {
            return $result->final_unit_cost;
        }

        $stock = Stock::whereHas('productVariant.product', function ($q) use ($productId) {
            $q->where('id', $productId);
        })
            ->where('storage_object_id', $storageObjectId)
            ->first();

        return $stock ? $stock->average_cost : 0;
    }

    /**
     * Взема името на варианта (цвят/размер)
     */
    private function getVariantName($variant)
    {
        $name = '';
        if ($variant->color) {
            $name .= $variant->color->name;
        }
        if ($variant->size) {
            $name .= ($name ? ' / ' : '') . $variant->size->name;
        }
        return $name ?: 'Стандартен';
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

    // Метод в ReportController
    public function stockStatus(Request $request)
    {
        $ownerId = $this->getOwnerId();
        $storageId = $request->get('storage_object_id');

        $query = Stock::with(['productVariant.product', 'storageObject'])
            ->whereHas('productVariant.product', fn($q) => $q->where('type', 'product'));

        if ($ownerId) {
            $query->whereHas('productVariant.product', fn($q) => $q->where('owner_id', $ownerId));
        }
        if ($storageId) {
            $query->where('storage_object_id', $storageId);
        }

        $stocks = $query->get();

        $totalValue = $stocks->sum(fn($s) => $s->quantity * $s->average_cost);
        $lowStock = $stocks->filter(fn($s) => $s->is_low_stock);

        return view('reports.stock-status', compact('stocks', 'totalValue', 'lowStock'));
    }

    public function hourlySales(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $sales = Receipt::whereDate('created_at', $date)
            ->where('type', 'sale')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as receipts'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return view('reports.hourly-sales', compact('sales', 'date'));
    }

    public function topProducts(Request $request)
    {
        $period = $request->get('period', 'month'); // week, month, year

        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $topProducts = ReceiptItem::select(
            'product_name_snapshot',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(total) as total_revenue')
        )
            ->whereHas('receipt', fn($q) => $q->where('created_at', '>=', $startDate))
            ->groupBy('product_name_snapshot')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        return view('reports.top-products', compact('topProducts', 'period'));
    }

    public function customerAnalysis(Request $request)
    {
        $customers = Client::withSum('receipts as total_spent', 'total_amount')
            ->withCount('receipts')
            ->where('is_active', true)
            ->orderByDesc('total_spent')
            ->get();

        $avgSpent = $customers->avg('total_spent');
        $totalCustomers = $customers->count();

        return view('reports.customer-analysis', compact('customers', 'avgSpent', 'totalCustomers'));
    }

    public function dailyTurnover(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        $daily = Receipt::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('type', 'sale')
            ->select(
                DB::raw('DAY(created_at) as day'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        return view('reports.daily-turnover', compact('daily', 'daysInMonth', 'month', 'year'));
    }

    public function monthlyProfit(Request $request)
    {
        $year = $request->get('year', now()->year);

        $revenue = Receipt::whereYear('created_at', $year)
            ->where('type', 'sale')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $costs = Purchase::whereYear('purchase_date', $year)
            ->select(DB::raw('MONTH(purchase_date) as month'), DB::raw('SUM(total) as total'))
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $months = ['Яну', 'Фев', 'Мар', 'Апр', 'Май', 'Юни', 'Юли', 'Авг', 'Сеп', 'Окт', 'Ное', 'Дек'];
        $report = [];

        foreach ($months as $i => $name) {
            $monthNum = $i + 1;
            $inc = $revenue[$monthNum]->total ?? 0;
            $out = $costs[$monthNum]->total ?? 0;
            $report[] = [
                'month' => $name,
                'revenue' => $inc,
                'costs' => $out,
                'profit' => $inc - $out,
                'margin' => $inc > 0 ? (($inc - $out) / $inc) * 100 : 0,
            ];
        }

        return view('reports.monthly-profit', compact('report', 'year'));
    }

    public function paymentMethods(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());

        $methods = Receipt::whereBetween('created_at', [$startDate, $endDate])
            ->where('type', 'sale')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        $totalCount = $methods->sum('count');
        $totalAmount = $methods->sum('total');

        return view('reports.payment-methods', compact('methods', 'totalCount', 'totalAmount', 'startDate', 'endDate'));
    }

    public function lowStockAlert()
    {
        $lowStocks = Stock::with(['productVariant.product', 'storageObject'])
            ->whereRaw('quantity <= min_quantity')
            ->where('quantity', '>', 0)
            ->get();

        $totalValue = $lowStocks->sum(fn($s) => ($s->min_quantity - $s->quantity) * $s->average_cost);

        return view('reports.low-stock', compact('lowStocks', 'totalValue'));
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

    /**
     * ОБОРОТИ ОТ SHOPPING MALL (сметки)
     */
    public function shoppingMallSales(Request $request)
    {
        $ownerId = $this->getOwnerId();

        // Обработка на бързи периоди
        $quickPeriod = $request->get('quick_period');

        if ($quickPeriod == 'month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($quickPeriod == 'quarter') {
            $startDate = now()->startOfQuarter();
            $endDate = now()->endOfQuarter();
        } elseif ($quickPeriod == 'half_year') {
            $startDate = now()->subMonths(6)->startOfDay();
            $endDate = now();
        } elseif ($quickPeriod == 'year') {
            $startDate = now()->startOfYear();
            $endDate = now()->endOfYear();
        } else {
            $startDate = $request->get('start_date')
                ? Carbon::parse($request->get('start_date'))->startOfDay()
                : now()->startOfMonth();

            $endDate = $request->get('end_date')
                ? Carbon::parse($request->get('end_date'))->endOfDay()
                : now();
        }

        // БАЗОВА ЗАЯВКА (без get или paginate)
        $baseQuery = ShoppingSession::whereBetween('created_at', [$startDate, $endDate])
            ->when($ownerId, function ($query) use ($ownerId) {
                return $query->where('owner_id', $ownerId);
            })
            ->with(['items', 'createdBy', 'paidBy']);

        // 1. ВСИЧКИ ДАННИ ЗА ОБОБЩЕНИЯТА (клонираме заявката)
        $allSessions = clone $baseQuery;
        $allSessions = $allSessions->get();

        $totalRevenue = $allSessions->sum('total_amount');
        $totalCompleted = $allSessions->where('status', 'completed')->sum('total_amount');
        $totalCancelled = $allSessions->where('status', 'cancelled')->sum('total_amount');
        $totalSessions = $allSessions->count();

        // 2. ПАГИНАЦИЯ (отделна заявка за детайлите)
        $sessions = $baseQuery->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($session) {
                return [
                    'session_token' => $session->session_token,
                    'customer_name' => $session->customer_name ?? 'Анонимен',
                    'created_at' => $session->created_at,
                    'paid_at' => $session->paid_at,
                    'status' => $session->status,
                    'total_amount' => $session->total_amount,
                    'paid_amount' => $session->paid_amount,
                    'payment_method' => $session->payment_method,
                    'items_count' => $session->items->count(),
                    'created_by' => $session->createdBy?->name,
                    'paid_by' => $session->paidBy?->name,
                ];
            });

        return view('reports.shopping-mall-sales', compact('sessions', 'startDate', 'endDate', 'totalRevenue', 'totalCompleted', 'totalCancelled', 'totalSessions'));
    }

    /**
     * ПРОДАЖБИ ПО ЩАНДОВЕ (KIOSK) ОТ SHOPPING MALL
     */
    public function kioskSales(Request $request)
    {
        $ownerId = $this->getOwnerId();

        // Обработка на бързи периоди
        $quickPeriod = $request->get('quick_period');

        if ($quickPeriod == 'month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($quickPeriod == 'quarter') {
            $startDate = now()->startOfQuarter();
            $endDate = now()->endOfQuarter();
        } elseif ($quickPeriod == 'half_year') {
            $startDate = now()->subMonths(6)->startOfDay();
            $endDate = now();
        } elseif ($quickPeriod == 'year') {
            $startDate = now()->startOfYear();
            $endDate = now()->endOfYear();
        } else {
            $startDate = $request->get('start_date')
                ? Carbon::parse($request->get('start_date'))->startOfDay()
                : now()->startOfMonth();

            $endDate = $request->get('end_date')
                ? Carbon::parse($request->get('end_date'))->endOfDay()
                : now();
        }

        // Вземаме всички артикули от завършени сметки
        $items = ShoppingSessionItem::whereHas('shoppingSession', function ($q) use ($startDate, $endDate, $ownerId) {
            $q->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed');
            if ($ownerId) {
                $q->where('owner_id', $ownerId);
            }
        })
            ->with(['kiosk', 'product'])
            ->get();

        // Групиране по щандове (като масив)
        $kioskSummary = $items->groupBy('kiosk_id')->map(function ($kioskItems, $kioskId) {
            $kiosk = $kioskItems->first()->kiosk;
            return [
                'kiosk_name' => $kiosk?->name ?? 'Неизвестен щанд',
                'items_count' => $kioskItems->count(),
                'total_quantity' => $kioskItems->sum('quantity'),
                'total_revenue' => $kioskItems->sum('total_price'),
            ];
        })->sortByDesc('total_revenue')->values();

        // Детайлен списък (като масив)
        $report = $items->map(function ($item) {
            return [
                'session_token' => $item->shoppingSession->session_token,
                'date' => $item->created_at,
                'kiosk_name' => $item->kiosk?->name ?? 'Неизвестен',
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'unit' => $item->unit,
            ];
        });

        return view('reports.kiosk-sales', compact('report', 'kioskSummary', 'startDate', 'endDate'));
    }

    /**
     * ПРОДАЖБИ ПО ПРОДУКТИ ОТ SHOPPING MALL
     */
    public function shoppingMallProductSales(Request $request)
    {
        $ownerId = $this->getOwnerId();

        // Обработка на бързи периоди
        $quickPeriod = $request->get('quick_period');

        if ($quickPeriod == 'month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($quickPeriod == 'quarter') {
            $startDate = now()->startOfQuarter();
            $endDate = now()->endOfQuarter();
        } elseif ($quickPeriod == 'half_year') {
            $startDate = now()->subMonths(6)->startOfDay();
            $endDate = now();
        } elseif ($quickPeriod == 'year') {
            $startDate = now()->startOfYear();
            $endDate = now()->endOfYear();
        } else {
            $startDate = $request->get('start_date')
                ? Carbon::parse($request->get('start_date'))->startOfDay()
                : now()->startOfMonth();

            $endDate = $request->get('end_date')
                ? Carbon::parse($request->get('end_date'))->endOfDay()
                : now();
        }

        // БАЗОВА ЗАЯВКА (без get или paginate)
        $baseQuery = ShoppingSessionItem::whereHas('shoppingSession', function ($q) use ($startDate, $endDate, $ownerId) {
            $q->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed');
            if ($ownerId) {
                $q->where('owner_id', $ownerId);
            }
        })
            ->with(['kiosk', 'product']);

        // 1. ВСИЧКИ ДАННИ ЗА ОБОБЩЕНИЯТА (клонираме заявката)
        $allItems = clone $baseQuery;
        $allItems = $allItems->get();

        $totalRevenue = $allItems->sum('total_price');
        $totalQuantity = $allItems->sum('quantity');
        $totalTransactions = $allItems->groupBy('shopping_session_id')->count();

        // 2. ГРУПИРАНЕ ПО ПРОДУКТИ (от всички данни)
        $productSummary = $allItems->groupBy('product_id')->map(function ($productItems, $productId) {
            $firstItem = $productItems->first();
            return [
                'product_name' => $firstItem->product_name,
                'unit' => $firstItem->unit ?? 'бр.',
                'quantity' => $productItems->sum('quantity'),
                'total_revenue' => $productItems->sum('total_price'),
            ];
        })->sortByDesc('total_revenue')->values();

        // 3. ПАГИНАЦИЯ (отделна заявка за детайлите)
        $report = $baseQuery->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($item) {
                return [
                    'session_token' => $item->shoppingSession->session_token,
                    'date' => $item->created_at,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'unit' => $item->unit,
                    'kiosk_name' => $item->kiosk?->name ?? 'Неизвестен',
                ];
            });

        return view('reports.shopping-mall-product-sales', compact(
            'report',
            'productSummary',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalQuantity',
            'totalTransactions'
        ));
    }
}