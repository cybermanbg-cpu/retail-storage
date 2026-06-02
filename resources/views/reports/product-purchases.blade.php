@extends('layouts.app')

@section('title', 'Доставки по продукти')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">🚚 Доставки по продукти</h1>
        <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
    </div>
    
    <!-- Филтър за период -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-1">От дата</label>
                <input type="date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}" class="border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">До дата</label>
                <input type="date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}" class="border rounded-lg px-3 py-2">
            </div>
            <div>
                <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">Филтрирай</button>
            </div>
        </form>
    </div>
    
    <!-- Таблица -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Продукт</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Артикул</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Закупено к-во</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Обща стойност</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ср. цена</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    $totalQuantity = 0;
                    $totalCost = 0;
                @endphp
                @foreach($report as $item)
                @php
                    $totalQuantity += $item['purchased_quantity'];
                    $totalCost += $item['total_cost'];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item['product_name'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item['sku'] }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($item['purchased_quantity'], 2) }} {{ $item['unit'] }}</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-orange-600">{{ number_format($item['total_cost'], 2) }} лв.</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($item['avg_cost'], 2) }} лв.</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-100">
                <tr>
                    <td colspan="2" class="px-6 py-3 text-sm font-bold text-gray-900">ОБЩО:</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">{{ number_format($totalQuantity, 2) }}</td>
                    <td class="px-6 py-3 text-sm font-bold text-right text-orange-700">{{ number_format($totalCost, 2) }} лв.</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">{{ $totalQuantity > 0 ? number_format($totalCost / $totalQuantity, 2) : 0 }} лв.</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection