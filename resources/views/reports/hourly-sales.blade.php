@extends('layouts.app')

@section('title', 'Продажби по часове')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">⏰ Продажби по часове</h1>
        <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
    </div>
    
    <!-- Филтър -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-1">Дата</label>
                <input type="date" name="date" value="{{ request('date', $date) }}" class="border rounded-lg px-3 py-2">
            </div>
            <div>
                <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">Филтрирай</button>
            </div>
        </form>
    </div>
    
    <!-- KPI карти -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-blue-500 mb-2">🕐</div>
            <div class="text-2xl font-bold">{{ $sales->sum('receipts') }}</div>
            <div class="text-gray-600">Брой продажби</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-green-500 mb-2">💰</div>
            <div class="text-2xl font-bold">{{ number_format($sales->sum('total'), 2) }} лв.</div>
            <div class="text-gray-600">Общ оборот</div>
        </div>
    </div>
    
    <!-- Таблица -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Час</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Брой продажби</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Оборот</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ср. стойност</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    $hours = range(0, 23);
                    $byHour = $sales->keyBy('hour');
                @endphp
                @foreach($hours as $hour)
                @php $data = $byHour[$hour] ?? null; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ sprintf('%02d:00 - %02d:59', $hour, $hour) }}</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format($data['receipts'] ?? 0) }}</td>
                    <td class="px-6 py-4 text-sm text-right {{ ($data['total'] ?? 0) > 0 ? 'font-semibold text-green-600' : '' }}">{{ number_format($data['total'] ?? 0, 2) }} лв.</td>
                    <td class="px-6 py-4 text-sm text-right">{{ number_format(($data['total'] ?? 0) / (($data['receipts'] ?? 0) ?: 1), 2) }} лв.</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-100">
                <tr>
                    <td class="px-6 py-3 text-sm font-bold text-gray-900">ОБЩО:</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">{{ number_format($sales->sum('receipts')) }}</td>
                    <td class="px-6 py-3 text-sm font-bold text-right text-green-700">{{ number_format($sales->sum('total'), 2) }} лв.</td>
                    <td class="px-6 py-3 text-sm font-bold text-right">{{ number_format($sales->sum('total') / max($sales->sum('receipts'), 1), 2) }} лв.</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection