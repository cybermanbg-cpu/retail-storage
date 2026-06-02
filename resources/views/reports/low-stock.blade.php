@extends('layouts.app')

@section('title', 'Нисък запас')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">⚠️ Сигнал за нисък запас</h1>
        <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
    </div>
    
    <!-- KPI карти -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 text-center bg-yellow-50">
            <div class="text-3xl text-yellow-500 mb-2">⚠️</div>
            <div class="text-2xl font-bold">{{ $lowStocks->count() }}</div>
            <div class="text-gray-600">Продукти с нисък запас</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-red-500 mb-2">📦</div>
            <div class="text-2xl font-bold">{{ number_format($lowStocks->sum('quantity'), 3) }}</div>
            <div class="text-gray-600">Общо бройки на склад</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-orange-500 mb-2">💰</div>
            <div class="text-2xl font-bold">{{ number_format($totalValue, 2) }} €</div>
            <div class="text-gray-600">Стойност за доставка</div>
        </div>
    </div>
    
    <!-- Таблица -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Продукт</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Обект</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Наличност</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Мин. ниво</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Необходими</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Прогнозна цена</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Стойност</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($lowStocks as $stock)
                @php
                    $needed = max(0, $stock->min_quantity - $stock->quantity);
                    $value = $needed * $stock->average_cost;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $stock->productVariant->product->name }}
                        @if($stock->productVariant->color || $stock->productVariant->size)
                            <span class="text-xs text-gray-500">
                                ({{ $stock->productVariant->color?->name ?? '' }} {{ $stock->productVariant->size?->name ?? '' }})
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $stock->storageObject->name }}</td>
                    <td class="px-6 py-4 text-sm text-right text-red-600 font-semibold">{{ number_format($stock->quantity, 3) }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($stock->min_quantity, 3) }}</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold">{{ number_format($needed, 3) }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($stock->average_cost, 2) }} €</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($value, 2) }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Препоръка за поръчка -->
    @if($lowStocks->count() > 0)
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <div class="text-2xl">📋</div>
            <div>
                <h3 class="font-semibold text-yellow-800">Препоръка за поръчка</h3>
                <p class="text-sm text-yellow-700 mt-1">
                    Препоръчва се да направите поръчка за <strong>{{ $lowStocks->count() }}</strong> продукта на обща стойност 
                    <strong>{{ number_format($totalValue, 2) }} €</strong>
                </p>
                <a href="{{ route('purchases.create') }}" class="inline-block mt-2 bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-yellow-700">
                    ➕ Направи поръчка
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection