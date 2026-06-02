@extends('layouts.app')

@section('title', 'Анализ на печалбата')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">💰 Анализ на печалбата</h1>
        <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
    </div>
    
    <!-- Филтър за период -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-1">От дата</label>
                <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">До дата</label>
                <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="border rounded-lg px-3 py-2">
            </div>
            <div>
                <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">Филтрирай</button>
            </div>
        </form>
    </div>
    
    <!-- KPI карти -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-blue-500 mb-2">💰</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($totalRevenue, 2) }} лв.</div>
            <div class="text-gray-600">Общ приход</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-orange-500 mb-2">📦</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($totalCOGS, 2) }} лв.</div>
            <div class="text-gray-600">Себестойност (COGS)</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-green-500 mb-2">📈</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format($totalProfit, 2) }} лв.</div>
            <div class="text-gray-600">Печалба</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-purple-500 mb-2">📊</div>
            <div class="text-2xl font-bold text-purple-600">{{ $totalRevenue > 0 ? number_format(($totalProfit / $totalRevenue) * 100, 1) : 0 }}%</div>
            <div class="text-gray-600">Марж на печалбата</div>
        </div>
    </div>
    
    <!-- Tabs -->
    <div x-data="{ tab: 'summary' }" class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-4">
                <button @click="tab = 'summary'" :class="{ 'border-primary-500 text-primary-600': tab == 'summary', 'border-transparent text-gray-500': tab != 'summary' }" class="py-2 px-4 border-b-2 font-medium text-sm">
                    📊 Обобщение по продукти
                </button>
                <button @click="tab = 'details'" :class="{ 'border-primary-500 text-primary-600': tab == 'details', 'border-transparent text-gray-500': tab != 'details' }" class="py-2 px-4 border-b-2 font-medium text-sm">
                    📋 Детайлна справка
                </button>
            </nav>
        </div>
        
        <!-- Обобщение по продукти -->
        <div x-show="tab == 'summary'" class="mt-6">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Продукт</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Количество</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Приход</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Себестойност</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Печалба</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Марж</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($productSummary as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product['product_name'] }}</td>
                            <td class="px-6 py-4 text-sm text-right">{{ number_format($product['quantity'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-green-600">{{ number_format($product['revenue'], 2) }} лв.</td>
                            <td class="px-6 py-4 text-sm text-right text-orange-600">{{ number_format($product['cogs'], 2) }} лв.</td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-blue-600">{{ number_format($product['profit'], 2) }} лв.</td>
                            <td class="px-6 py-4 text-sm text-right">
                                <span class="px-2 py-1 rounded-full text-xs {{ $product['margin_percent'] >= 20 ? 'bg-green-100 text-green-800' : ($product['margin_percent'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ number_format($product['margin_percent'], 1) }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100">
                        <tr>
                            <td class="px-6 py-3 text-sm font-bold text-gray-900">ОБЩО:</td>
                            <td class="px-6 py-3 text-sm font-bold text-right">{{ number_format($productSummary->sum('quantity'), 2) }}</td>
                            <td class="px-6 py-3 text-sm font-bold text-right text-green-700">{{ number_format($totalRevenue, 2) }} лв.</td>
                            <td class="px-6 py-3 text-sm font-bold text-right text-orange-700">{{ number_format($totalCOGS, 2) }} лв.</td>
                            <td class="px-6 py-3 text-sm font-bold text-right text-blue-700">{{ number_format($totalProfit, 2) }} лв.</td>
                            <td class="px-6 py-3 text-sm font-bold text-right">{{ $totalRevenue > 0 ? number_format(($totalProfit / $totalRevenue) * 100, 1) : 0 }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Детайлна справка -->
        <div x-show="tab == 'details'" class="mt-6">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Разписка</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Продукт</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Вариант</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">К-во</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Цена</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Приход</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Себест.</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Печалба</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Марж</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($report as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item['receipt_number'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item['date']->format('d.m.Y') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item['product_name'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item['variant'] }}</td>
                            <td class="px-6 py-4 text-sm text-right">{{ number_format($item['quantity'], 2) }} {{ $item['unit'] }}</td>
                            <td class="px-6 py-4 text-sm text-right">{{ number_format($item['selling_price'], 2) }} лв.</td>
                            <td class="px-6 py-4 text-sm text-right text-green-600">{{ number_format($item['revenue'], 2) }} лв.</td>
                            <td class="px-6 py-4 text-sm text-right text-orange-600">{{ number_format($item['cogs'], 2) }} лв.</td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-blue-600">{{ number_format($item['profit'], 2) }} лв.</td>
                            <td class="px-6 py-4 text-sm text-right">
                                <span class="px-2 py-1 rounded-full text-xs {{ $item['margin_percent'] >= 20 ? 'bg-green-100 text-green-800' : ($item['margin_percent'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ number_format($item['margin_percent'], 1) }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100">
                        <tr>
                            <td colspan="6" class="px-6 py-3 text-sm font-bold text-gray-900">ОБЩО:</td>
                            <td class="px-6 py-3 text-sm font-bold text-right text-green-700">{{ number_format($totalRevenue, 2) }} лв.</td>
                            <td class="px-6 py-3 text-sm font-bold text-right text-orange-700">{{ number_format($totalCOGS, 2) }} лв.</td>
                            <td class="px-6 py-3 text-sm font-bold text-right text-blue-700">{{ number_format($totalProfit, 2) }} лв.</td>
                            <td class="px-6 py-3 text-sm font-bold text-right">{{ $totalRevenue > 0 ? number_format(($totalProfit / $totalRevenue) * 100, 1) : 0 }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
@endpush