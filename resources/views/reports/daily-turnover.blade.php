@extends('layouts.app')

@section('title', 'Дневен оборот')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📅 Дневен оборот</h1>
        <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
    </div>
    
    <!-- Филтър -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-1">Месец</label>
                <select name="month" class="border rounded-lg px-3 py-2">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month', $month) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
            </div>
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
    
    <!-- Календарна таблица -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Пн</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Вт</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ср</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Чт</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Пт</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Сб</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Нд</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $firstDay = Carbon\Carbon::create($year, $month, 1);
                    $lastDay = $firstDay->copy()->endOfMonth();
                    $startOfWeek = $firstDay->copy()->startOfWeek(Carbon\Carbon::MONDAY);
                    $endOfWeek = $lastDay->copy()->endOfWeek(Carbon\Carbon::SUNDAY);
                    $currentDay = $startOfWeek->copy();
                @endphp
                
                @while($currentDay <= $endOfWeek)
                    <tr>
                        @for($i = 0; $i < 7; $i++)
                            @php
                                $day = $currentDay->copy();
                                $isCurrentMonth = $day->month == $month;
                                $hasData = $daily[$day->day] ?? null;
                                $total = $hasData ? $hasData->total : 0;
                            @endphp
                            <td class="px-2 py-3 border text-center align-top {{ !$isCurrentMonth ? 'bg-gray-50' : '' }}" style="min-width: 100px;">
                                <div class="text-sm {{ !$isCurrentMonth ? 'text-gray-400' : 'font-medium' }}">{{ $day->format('j') }}</div>
                                @if($isCurrentMonth)
                                    <div class="text-xs {{ $total > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                                        {{ number_format($total, 2) }} лв.
                                    </div>
                                @endif
                            </td>
                            @php $currentDay->addDay(); @endphp
                        @endfor
                    </tr>
                @endwhile
            </tbody>
        </table>
    </div>
    
    <!-- Обобщение -->
    <div class="mt-6 bg-white rounded-lg shadow p-4">
        <div class="flex justify-between items-center">
            <span class="font-semibold">Общ оборот за месеца:</span>
            <span class="text-2xl font-bold text-green-600">{{ number_format($daily->sum('total'), 2) }} лв.</span>
        </div>
        <div class="flex justify-between items-center mt-2 text-sm text-gray-600">
            <span>Среден дневен оборот:</span>
            <span>{{ number_format($daily->sum('total') / max($daysInMonth, 1), 2) }} лв.</span>
        </div>
        <div class="flex justify-between items-center mt-2 text-sm text-gray-600">
            <span>Най-висок дневен оборот:</span>
            <span>{{ number_format($daily->max('total') ?? 0, 2) }} лв.</span>
        </div>
    </div>
</div>
@endsection