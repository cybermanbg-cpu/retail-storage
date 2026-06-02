@extends('layouts.app')

@section('title', 'Обороти по касиери')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">🪪 Обороти по касиери</h1>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Касиер</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Брой продажби</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Оборот (с ДДС)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ДДС</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Уникални клиенти</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ср. стойност</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    $totalRevenue = 0;
                    $totalVat = 0;
                    $totalReceipts = 0;
                @endphp
                @foreach($report as $item)
                @php
                    $totalRevenue += $item['total_revenue'];
                    $totalVat += $item['total_vat'];
                    $totalReceipts += $item['receipt_count'];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item['cashier_name'] }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($item['receipt_count']) }}</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-green-600">{{ number_format($item['total_revenue'], 2) }} лв.</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($item['total_vat'], 2) }} лв.</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($item['unique_clients']) }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($item['avg_receipt'], 2) }} лв.</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-100">
                <tr>
                    <td class="px-6 py-3 text-sm font-bold text-gray-900">ОБЩО:</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">{{ number_format($totalReceipts) }}</td>
                    <td class="px-6 py-3 text-sm font-bold text-right text-green-700">{{ number_format($totalRevenue, 2) }} лв.</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">{{ number_format($totalVat, 2) }} лв.</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">-</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">{{ $totalReceipts > 0 ? number_format($totalRevenue / $totalReceipts, 2) : 0 }} лв.</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection