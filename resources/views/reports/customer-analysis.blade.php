@extends('layouts.app')

@section('title', 'Анализ на клиенти')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">👥 Анализ на клиенти (RFM)</h1>
        <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
    </div>
    
    <!-- KPI карти -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-blue-500 mb-2">👥</div>
            <div class="text-2xl font-bold">{{ number_format($totalCustomers) }}</div>
            <div class="text-gray-600">Общо клиенти</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-green-500 mb-2">💰</div>
            <div class="text-2xl font-bold">{{ number_format($customers->sum('total_spent'), 2) }} €</div>
            <div class="text-gray-600">Общ оборот</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-purple-500 mb-2">📊</div>
            <div class="text-2xl font-bold">{{ number_format($avgSpent, 2) }} €</div>
            <div class="text-gray-600">Ср. оборот/клиент</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-yellow-500 mb-2">🏆</div>
            <div class="text-2xl font-bold">{{ $customers->first()->name ?? '—' }}</div>
            <div class="text-gray-600">Топ клиент</div>
        </div>
    </div>
    
    <!-- Таблица -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Клиент</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Телефон</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Брой покупки</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Общ оборот</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ср. стойност</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Класификация</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($customers as $customer)
                @php
                    $totalSpent = $customer->total_spent ?? 0;
                    $receiptCount = $customer->receipts_count ?? 0;
                    $avgOrder = $receiptCount > 0 ? $totalSpent / $receiptCount : 0;
                    
                    // RFM класификация
                    if ($totalSpent > 1000) $segment = '⭐ Платинен';
                    elseif ($totalSpent > 500) $segment = '🥇 Златен';
                    elseif ($totalSpent > 200) $segment = '🥈 Сребърен';
                    elseif ($totalSpent > 50) $segment = '🥉 Бронзов';
                    else $segment = '🟢 Нов';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $customer->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $customer->phone ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($receiptCount) }}</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-green-600">{{ number_format($totalSpent, 2) }} €</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($avgOrder, 2) }} €</td>
                    <td class="px-6 py-4 text-sm text-center">
                        <span class="px-2 py-1 rounded-full text-xs {{ 
                            $segment == '⭐ Платинен' ? 'bg-purple-100 text-purple-800' : 
                            ($segment == '🥇 Златен' ? 'bg-yellow-100 text-yellow-800' : 
                            ($segment == '🥈 Сребърен' ? 'bg-gray-100 text-gray-800' : 
                            ($segment == '🥉 Бронзов' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'))) }}">
                            {{ $segment }}
                        </span>
                    </td>
                </td>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection