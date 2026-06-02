@extends('layouts.app')

@section('title', 'Складова наличност')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📦 Складова наличност</h1>
        <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
    </div>
    
    <!-- Филтри -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-1">Обект</label>
                <select name="storage_object_id" class="border rounded-lg px-3 py-2">
                    <option value="">Всички обекти</option>
                    @foreach($storageObjects ?? [] as $object)
                        <option value="{{ $object->id }}" {{ request('storage_object_id') == $object->id ? 'selected' : '' }}>{{ $object->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">Филтрирай</button>
            </div>
        </form>
    </div>
    
    <!-- KPI карти -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-blue-500 mb-2">📦</div>
            <div class="text-2xl font-bold">{{ number_format($stocks->sum('quantity'), 2) }}</div>
            <div class="text-gray-600">Общо бройки на склад</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-green-500 mb-2">💰</div>
            <div class="text-2xl font-bold">{{ number_format($totalValue, 2) }} €</div>
            <div class="text-gray-600">Стойност на наличността</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-red-500 mb-2">⚠️</div>
            <div class="text-2xl font-bold">{{ $lowStock->count() }}</div>
            <div class="text-gray-600">Продукти с нисък запас</div>
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
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ср. цена</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Стойност</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Статус</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($stocks as $stock)
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
                    <td class="px-6 py-4 text-sm text-right font-semibold">{{ number_format($stock->quantity, 3) }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($stock->min_quantity, 3) }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($stock->average_cost, 2) }} €</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($stock->quantity * $stock->average_cost, 2) }} €</td>
                    <td class="px-6 py-4 text-sm text-center">
                        @if($stock->quantity <= 0)
                            <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">✗ Няма</span>
                        @elseif($stock->is_low_stock)
                            <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">⚠️ Нисък</span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">✓ Нормален</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection