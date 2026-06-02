@extends('layouts.app')

@section('title', 'Продажби по начини на плащане')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">💳 Продажби по начини на плащане</h1>
        <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
    </div>
    
    <!-- Филтър -->
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-blue-500 mb-2">📊</div>
            <div class="text-2xl font-bold">{{ number_format($totalCount) }}</div>
            <div class="text-gray-600">Общ брой продажби</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-green-500 mb-2">💰</div>
            <div class="text-2xl font-bold">{{ number_format($totalAmount, 2) }} лв.</div>
            <div class="text-gray-600">Общ оборот</div>
        </div>
    </div>
    
    <!-- Графични карти -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach($methods as $method)
        @php
            $percent = $totalAmount > 0 ? ($method->total / $totalAmount) * 100 : 0;
            $icon = match($method->payment_method) {
                'cash' => '💰',
                'card' => '💳',
                'bank_transfer' => '🏦',
                default => '❓'
            };
            $color = match($method->payment_method) {
                'cash' => 'bg-green-500',
                'card' => 'bg-blue-500',
                'bank_transfer' => 'bg-purple-500',
                default => 'bg-gray-500'
            };
            $label = match($method->payment_method) {
                'cash' => 'В брой',
                'card' => 'Карта',
                'bank_transfer' => 'Банков превод',
                default => $method->payment_method
            };
        @endphp
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="{{ $color }} px-4 py-2 text-white">{{ $label }}</div>
            <div class="p-4 text-center">
                <div class="text-4xl mb-2">{{ $icon }}</div>
                <div class="text-2xl font-bold">{{ number_format($method->total, 2) }} лв.</div>
                <div class="text-sm text-gray-600">{{ number_format($method->count) }} продажби</div>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="{{ $color }} h-2 rounded-full" style="width: {{ $percent }}%"></div>
                </div>
                <div class="text-sm mt-1">{{ number_format($percent, 1) }}% от общия оборот</div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Таблица -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Начин на плащане</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Брой продажби</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Оборот</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ср. стойност</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">% от общия</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($methods as $method)
                @php
                    $percentAmount = $totalAmount > 0 ? ($method->total / $totalAmount) * 100 : 0;
                    $percentCount = $totalCount > 0 ? ($method->count / $totalCount) * 100 : 0;
                    $label = match($method->payment_method) {
                        'cash' => '💰 В брой',
                        'card' => '💳 Карта',
                        'bank_transfer' => '🏦 Банков превод',
                        default => $method->payment_method
                    };
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $label }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($method->count) }}
                        <span class="text-xs text-gray-400">({{ number_format($percentCount, 1) }}%)</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-green-600">{{ number_format($method->total, 2) }} лв.
                        <span class="text-xs text-gray-400">({{ number_format($percentAmount, 1) }}%)</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($method->count > 0 ? $method->total / $method->count : 0, 2) }} лв.</td>
                    <td class="px-6 py-4 text-sm text-right">
                        <div class="flex items-center justify-end gap-2">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $percentAmount }}%"></div>
                            </div>
                            <span class="text-xs">{{ number_format($percentAmount, 1) }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection