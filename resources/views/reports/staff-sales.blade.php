@extends('layouts.app')

@section('title', 'Оборот по служители')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">👥 Оборот по служители</h1>
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
    
    <!-- Карти с KPI -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ number_format($report->sum('total_revenue'), 2) }} €</div>
            <div class="text-sm text-gray-600">Общ оборот</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ number_format($report->sum('receipt_count')) }}</div>
            <div class="text-sm text-gray-600">Брой продажби</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $report->avg('avg_receipt') ? number_format($report->avg('avg_receipt'), 2) : 0 }} €</div>
            <div class="text-sm text-gray-600">Ср. стойност</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-orange-600">{{ $report->where('role', 'Касиер')->count() }}</div>
            <div class="text-sm text-gray-600">Активни касиери</div>
        </div>
    </div>
    
    <!-- Таблица -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Служител</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Роля</th>
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
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item['staff_name'] }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 rounded-full text-xs {{ $item['role'] == 'Супер администратор' ? 'bg-red-100 text-red-800' : ($item['role'] == 'Собственик' ? 'bg-purple-100 text-purple-800' : ($item['role'] == 'Мениджър' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800')) }}">
                            {{ $item['role'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($item['receipt_count']) }}</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-green-600">{{ number_format($item['total_revenue'], 2) }} €</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($item['total_vat'], 2) }} €</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($item['unique_clients']) }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($item['avg_receipt'], 2) }} €</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-100">
                <tr>
                    <td colspan="2" class="px-6 py-3 text-sm font-bold text-gray-900">ОБЩО:</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">{{ number_format($totalReceipts) }}</td>
                    <td class="px-6 py-3 text-sm font-bold text-right text-green-700">{{ number_format($totalRevenue, 2) }} €</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">{{ number_format($totalVat, 2) }} €</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">-</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">{{ $totalReceipts > 0 ? number_format($totalRevenue / $totalReceipts, 2) : 0 }} €</td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <!-- Лидерборд -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow p-4 text-white text-center">
            <div class="text-3xl mb-2">🥇</div>
            <div class="font-bold text-lg">{{ $report->first()['staff_name'] ?? '—' }}</div>
            <div class="text-sm opacity-90">Най-висок оборот</div>
            <div class="text-xl font-bold">{{ number_format($report->first()['total_revenue'] ?? 0, 2) }} €</div>
        </div>
        <div class="bg-gradient-to-r from-gray-400 to-gray-500 rounded-lg shadow p-4 text-white text-center">
            <div class="text-3xl mb-2">🥈</div>
            <div class="font-bold text-lg">{{ $report->skip(1)->first()['staff_name'] ?? '—' }}</div>
            <div class="text-sm opacity-90">Втори най-висок оборот</div>
            <div class="text-xl font-bold">{{ number_format($report->skip(1)->first()['total_revenue'] ?? 0, 2) }} €</div>
        </div>
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow p-4 text-white text-center">
            <div class="text-3xl mb-2">🥉</div>
            <div class="font-bold text-lg">{{ $report->skip(2)->first()['staff_name'] ?? '—' }}</div>
            <div class="text-sm opacity-90">Трети най-висок оборот</div>
            <div class="text-xl font-bold">{{ number_format($report->skip(2)->first()['total_revenue'] ?? 0, 2) }} €</div>
        </div>
    </div>
</div>
@endsection