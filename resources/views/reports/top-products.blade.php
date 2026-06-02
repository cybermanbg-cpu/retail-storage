@extends('layouts.app')

@section('title', 'Топ 10 продукти')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">🏆 Топ 10 най-продавани продукти</h1>
        <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
    </div>
    
    <!-- Филтър -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-1">Период</label>
                <select name="period" class="border rounded-lg px-3 py-2">
                    <option value="week" {{ request('period', 'month') == 'week' ? 'selected' : '' }}>Тази седмица</option>
                    <option value="month" {{ request('period', 'month') == 'month' ? 'selected' : '' }}>Този месец</option>
                    <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>Тази година</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">Филтрирай</button>
            </div>
        </form>
    </div>
    
    <!-- Лидерборд -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4 text-center">🏆 Топ 3 продукти</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($topProducts->take(3) as $index => $product)
            <div class="text-center p-4 rounded-lg {{ $index == 0 ? 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white' : ($index == 1 ? 'bg-gradient-to-r from-gray-400 to-gray-500 text-white' : 'bg-gradient-to-r from-orange-500 to-orange-600 text-white') }}">
                <div class="text-4xl mb-2">{{ $index == 0 ? '🥇' : ($index == 1 ? '🥈' : '🥉') }}</div>
                <div class="font-bold text-lg">{{ $product->product_name_snapshot }}</div>
                <div class="text-sm opacity-90">{{ number_format($product->total_quantity, 2) }} бр.</div>
                <div class="text-xl font-bold">{{ number_format($product->total_revenue, 2) }} лв.</div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Таблица -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Продукт</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Количество</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Оборот</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">% от общия</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    $totalQuantity = $topProducts->sum('total_quantity');
                    $totalRevenue = $topProducts->sum('total_revenue');
                @endphp
                @foreach($topProducts as $index => $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->product_name_snapshot }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($product->total_quantity, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-green-600">{{ number_format($product->total_revenue, 2) }} лв.</td>
                    <td class="px-6 py-4 text-sm text-right">
                        <div class="flex items-center justify-end gap-2">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $totalRevenue > 0 ? ($product->total_revenue / $totalRevenue) * 100 : 0 }}%"></div>
                            </div>
                            <span class="text-xs">{{ $totalRevenue > 0 ? number_format(($product->total_revenue / $totalRevenue) * 100, 1) : 0 }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-100">
                <tr>
                    <td colspan="2" class="px-6 py-3 text-sm font-bold text-gray-900">ОБЩО:</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">{{ number_format($totalQuantity, 2) }}</td>
                    <td class="px-6 py-3 text-sm font-bold text-right text-green-700">{{ number_format($totalRevenue, 2) }} лв.</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">100%</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection