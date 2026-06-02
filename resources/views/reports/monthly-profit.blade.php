@extends('layouts.app')

@section('title', 'Месечна печалба')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📊 Месечна печалба</h1>
        <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
    </div>
    
    <!-- Филтър -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-1">Година</label>
                <select name="year" class="border rounded-lg px-3 py-2">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ request('year', $year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
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
            <div class="text-3xl text-green-500 mb-2">💰</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format(collect($report)->sum('revenue'), 2) }} €</div>
            <div class="text-gray-600">Общ приход</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-red-500 mb-2">📦</div>
            <div class="text-2xl font-bold text-red-600">{{ number_format(collect($report)->sum('costs'), 2) }} €</div>
            <div class="text-gray-600">Общи разходи</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-3xl text-blue-500 mb-2">📈</div>
            <div class="text-2xl font-bold text-blue-600">{{ number_format(collect($report)->sum('profit'), 2) }} €</div>
            <div class="text-gray-600">Обща печалба</div>
        </div>
    </div>
    
    <!-- Таблица -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Месец</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Приход</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Разходи</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Печалба</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Марж</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($report as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item['month'] }}</td>
                    <td class="px-6 py-4 text-sm text-right text-green-600">{{ number_format($item['revenue'], 2) }} €</td>
                    <td class="px-6 py-4 text-sm text-right text-red-600">{{ number_format($item['costs'], 2) }} €</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-blue-600">{{ number_format($item['profit'], 2) }} €</td>
                    <td class="px-6 py-4 text-sm text-right">
                        <span class="px-2 py-1 rounded-full text-xs {{ $item['margin'] >= 20 ? 'bg-green-100 text-green-800' : ($item['margin'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ number_format($item['margin'], 1) }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection