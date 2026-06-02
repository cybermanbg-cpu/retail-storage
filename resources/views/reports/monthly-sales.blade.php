@extends('layouts.app')

@section('title', 'Месечни обороти')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📅 Месечни обороти</h1>
        <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
    </div>
    
    <!-- Филтър за година -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-1">Година</label>
                <select name="year" class="border rounded-lg px-3 py-2">
                    @for($i = now()->year; $i >= now()->year - 5; $i--)
                        <option value="{{ $i }}" {{ request('year', now()->year) == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Месец</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Брой продажби</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Приход</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ДДС</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Разходи</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Печалба</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Марж</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    $totalRevenue = 0;
                    $totalCost = 0;
                    $totalProfit = 0;
                @endphp
                @foreach($report as $item)
                @php
                    $totalRevenue += $item['revenue'];
                    $totalCost += $item['cost'];
                    $totalProfit += $item['profit'];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item['month'] }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($item['receipt_count']) }}</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-green-600">{{ number_format($item['revenue'], 2) }} лв.</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($item['vat'], 2) }} лв.</td>
                    <td class="px-6 py-4 text-sm text-right text-orange-600">{{ number_format($item['cost'], 2) }} лв.</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-blue-600">{{ number_format($item['profit'], 2) }} лв.</td>
                    <td class="px-6 py-4 text-sm text-right">
                        <span class="px-2 py-1 rounded-full text-xs {{ $item['margin'] >= 20 ? 'bg-green-100 text-green-800' : ($item['margin'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ number_format($item['margin'], 1) }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-100">
                <tr>
                    <td class="px-6 py-3 text-sm font-bold text-gray-900">ОБЩО:</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">-</td>
                    <td class="px-6 py-3 text-sm font-bold text-right text-green-700">{{ number_format($totalRevenue, 2) }} лв.</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">-</td>
                    <td class="px-6 py-3 text-sm font-bold text-right text-orange-700">{{ number_format($totalCost, 2) }} лв.</td>
                    <td class="px-6 py-3 text-sm font-bold text-right text-blue-700">{{ number_format($totalProfit, 2) }} лв.</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">{{ $totalRevenue > 0 ? number_format(($totalProfit / $totalRevenue) * 100, 1) : 0 }}%</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection