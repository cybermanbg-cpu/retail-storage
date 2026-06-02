@extends('layouts.app')

@section('title', 'Начало - Retail Storage System')

@section('content')
<div class="container mx-auto px-4 py-12">
    <!-- Hero секция -->
    <div class="text-center mb-12">
        <h1 class="text-5xl font-bold text-gray-800 mb-4">
            Управление на продажбите
        </h1>
        <p class="text-xl text-gray-600 mb-8">
            Лесна и бърза POS система за вашия бизнес
        </p>
        <a href="{{ route('pos.index') }}" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white text-lg px-8 py-3 rounded-lg transition duration-300">
            <i class="fas fa-cash-register mr-2"></i>
            Стартирай POS
        </a>
    </div>

    <!-- Статистики (само за логнати потребители) -->
    @auth
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-12">
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-4xl text-blue-500 mb-3">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800">{{ $totalProducts ?? 0 }}</div>
            <div class="text-gray-600">Активни продукта</div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-4xl text-cyan-500 mb-3">
                <i class="fas fa-users"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800">{{ $totalClients ?? 0 }}</div>
            <div class="text-gray-600">Клиенти</div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-4xl text-green-500 mb-3">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($todaySales ?? 0, 2) }} €</div>
            <div class="text-gray-600">Продажби днес</div>
            @if(isset($todayGrowth))
                <div class="text-xs {{ $todayGrowth >= 0 ? 'text-green-500' : 'text-red-500' }} mt-1">
                    {{ $todayGrowth >= 0 ? '↑' : '↓' }} {{ number_format(abs($todayGrowth), 1) }}% от вчера
                </div>
            @endif
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-4xl text-purple-500 mb-3">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($totalSales ?? 0, 2) }} €</div>
            <div class="text-gray-600">Общо продажби</div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-4xl text-yellow-500 mb-3">
                <i class="fas fa-clock"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800">{{ $incompleteSales ?? 0 }}</div>
            <div class="text-gray-600">Незавършени продажби</div>
        </div>
    </div>

    <!-- Месечен оборот карта -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800">📅 Месечен оборот</h2>
                <span class="text-2xl">📊</span>
            </div>
            <div class="text-3xl font-bold text-blue-600">{{ number_format($monthSales ?? 0, 2) }} €</div>
            @if(isset($monthGrowth))
                <div class="text-sm {{ $monthGrowth >= 0 ? 'text-green-500' : 'text-red-500' }} mt-1">
                    {{ $monthGrowth >= 0 ? '↑' : '↓' }} {{ number_format(abs($monthGrowth), 1) }}% спрямо предходния месец
                </div>
            @endif
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800">👥 Активни касиери</h2>
                <span class="text-2xl">🪪</span>
            </div>
            <div class="text-3xl font-bold text-yellow-600">{{ $activeCashiers ?? 0 }}</div>
            <div class="text-gray-500 text-sm">касиери с продажби този месец</div>
        </div>
    </div>

    <!-- Топ касиер за месеца -->
    @if($topCashierOfMonth)
    <div class="bg-gradient-to-r from-yellow-500 to-amber-600 rounded-lg shadow-lg overflow-hidden mb-8">
        <div class="px-6 py-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm opacity-90">🏆 Касиер на месеца</div>
                    <div class="text-2xl font-bold">{{ $topCashierOfMonth->name }}</div>
                    <div class="text-xl font-semibold mt-2">{{ number_format($topCashierOfMonth->monthly_revenue ?? 0, 2) }} €</div>
                    <div class="text-sm opacity-90">оборот за месеца</div>
                </div>
                <div class="text-6xl">👑</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Топ 5 продукта за месеца -->
    @if(isset($topProducts) && $topProducts->count() > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-3">
            <h2 class="text-white font-semibold text-lg">
                <i class="fas fa-chart-simple mr-2"></i> Топ 5 продукта за месеца
            </h2>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($topProducts as $index => $product)
            <div class="p-4 flex justify-between items-center hover:bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold">
                        {{ $index + 1 }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">{{ $product->product_name }}</div>
                        <div class="text-xs text-gray-500">{{ number_format($product->total_quantity, 2) }} бр.</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-green-600">{{ number_format($product->total_revenue, 2) }} €</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Сигнал за нисък запас -->
    @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-3">
            <h2 class="text-white font-semibold text-lg">
                <i class="fas fa-exclamation-triangle mr-2"></i> ⚠️ Сигнал за нисък запас
            </h2>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($lowStockProducts as $stock)
            <div class="p-4 flex justify-between items-center hover:bg-gray-50">
                <div>
                    <div class="font-semibold text-gray-800">{{ $stock->productVariant?->product?->name ?? 'N/A' }}</div>
                    <div class="text-xs text-gray-500">{{ $stock->storageObject?->name ?? '' }}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-orange-600">{{ number_format($stock->quantity, 3) }} / {{ number_format($stock->min_quantity, 3) }}</div>
                    <div class="text-xs text-gray-400">мин. ниво</div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="p-3 bg-red-50 text-center">
            <a href="{{ route('reports.low-stock') }}" class="text-red-600 text-sm hover:underline">Виж всички →</a>
        </div>
    </div>
    @endif

    <!-- ======================================== -->
    <!-- ПЕРСОНАЛЕН ОБОРОТ НА КАСИЕРА -->
    <!-- ======================================== -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-3">
            <h2 class="text-white font-semibold text-lg">
                <i class="fas fa-user mr-2"></i> Моят оборот
            </h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($myTodayTurnover, 2) }} €</div>
                    <div class="text-sm text-gray-600">Днес</div>
                    <div class="text-xs text-gray-400">{{ $myTodaySalesCount }} продажби</div>
                </div>
                <div class="text-center p-3 bg-indigo-50 rounded-lg">
                    <div class="text-2xl font-bold text-indigo-600">{{ number_format($myWeekTurnover, 2) }} €</div>
                    <div class="text-sm text-gray-600">Тази седмица</div>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($myMonthTurnover, 2) }} €</div>
                    <div class="text-sm text-gray-600">Този месец</div>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($myTotalTurnover, 2) }} €</div>
                    <div class="text-sm text-gray-600">Общо</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================== -->
    <!-- ОБОРОТИ ПО КАСИЕРИ ЗА ДЕНЯ (само за admin/owner) -->
    <!-- ======================================== -->
    @if($isAdmin && $cashierTurnovers->count() > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-3">
            <h2 class="text-white font-semibold text-lg">
                <i class="fas fa-users mr-2"></i> Оборот по касиери - днес
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Касиер</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Брой продажби</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Оборот</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% от общия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $totalDailyTurnover = $cashierTurnovers->sum('today_turnover');
                    @endphp
                    @foreach($cashierTurnovers as $cashier)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $cashier['name'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $cashier['today_sales_count'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                            {{ number_format($cashier['today_turnover'], 2) }} €
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($totalDailyTurnover > 0)
                                {{ round(($cashier['today_turnover'] / $totalDailyTurnover) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900">Общо</td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900">{{ $cashierTurnovers->sum('today_sales_count') }}</td>
                        <td class="px-6 py-3 text-sm font-semibold text-green-600">{{ number_format($totalDailyTurnover, 2) }} €</td>
                        <td class="px-6 py-3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- ТОП 5 КАСИЕРИ ЗА МЕСЕЦА -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-3">
            <h2 class="text-white font-semibold text-lg">
                <i class="fas fa-trophy mr-2"></i> Топ 5 касиери за месеца
            </h2>
        </div>
        <div class="p-4">
            <div class="space-y-3">
                @foreach($topCashiers as $index => $cashier)
                <div class="flex items-center justify-between p-3 {{ $index == 0 ? 'bg-yellow-50' : ($index == 1 ? 'bg-gray-50' : ($index == 2 ? 'bg-orange-50' : 'bg-white')) }} rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 flex items-center justify-center rounded-full 
                            {{ $index == 0 ? 'bg-yellow-500' : ($index == 1 ? 'bg-gray-400' : ($index == 2 ? 'bg-orange-500' : 'bg-blue-500')) }} text-white font-bold">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800">{{ $cashier['name'] }}</div>
                            <div class="text-xs text-gray-500">Касиер</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-green-600">{{ number_format($cashier['monthly_turnover'], 2) }} €</div>
                        <div class="text-xs text-gray-500">оборот за месеца</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Последни продажби -->
    @if(isset($recentSales) && $recentSales->count() > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-3">
            <h2 class="text-white font-semibold text-lg">
                <i class="fas fa-history mr-2"></i> Последни продажби
            </h2>
        </div>
        <div class="divide-y divide-gray-100 max-h-[400px] overflow-y-auto">
            @foreach($recentSales as $sale)
            <div class="p-4 flex justify-between items-center hover:bg-gray-50">
                <div>
                    <div class="font-semibold text-gray-800">{{ $sale->receipt_number }}</div>
                    <div class="text-xs text-gray-500">{{ $sale->created_at->format('d.m.Y H:i') }}</div>
                    <div class="text-xs text-gray-500">{{ $sale->user?->name }} • {{ $sale->storageObject?->name }}</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-green-600">{{ number_format($sale->total_amount, 2) }} €</div>
                    <div class="text-xs text-gray-500">{{ $sale->client?->name ?? 'Анонимен' }}</div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="p-3 bg-gray-50 text-center">
            <a href="{{ route('reports.index') }}" class="text-primary-600 text-sm hover:underline">Виж всички доклади →</a>
        </div>
    </div>
    @endif

    <!-- Активни колички -->
    @if(isset($activeCarts) && $activeCarts->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Активни продажби в реално време</h2>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Касиер</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Обект</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Количка</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Брой артикули</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сума</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Последна активност</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($activeCarts as $cart)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $cart->cashier?->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $cart->storageObject?->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $cart->cart_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ count($cart->items ?? []) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-primary-600">
                            {{ number_format($cart->total_amount, 2) }} €
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $cart->updated_at->diffForHumans() }}
                        </td>
                    </td>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endauth

    <!-- Последни продукти (за всички) -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Последни добавени продукти</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($latestProducts as $product)
            <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition">
                <div class="font-semibold text-lg text-gray-800">{{ $product->name }}</div>
                <div class="text-sm text-gray-500">Артикул: {{ $product->sku }}</div>
                <div class="text-xl font-bold text-primary-600 mt-2">{{ number_format($product->base_price, 2) }} €</div>
                @if($product->has_variants)
                    <div class="text-xs text-gray-400 mt-1">✓ С варианти (цвят/размер)</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection