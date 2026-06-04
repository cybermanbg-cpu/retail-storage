@extends('layouts.app')

@section('title', 'Мол POS - Продажби по щандове')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">🏪 Продажби по щандове</h1>
            <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
        </div>

        <!-- Филтри -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('reports.kiosk-sales') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">От дата</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">До дата</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Бърз период</label>
                    <select name="quick_period" class="w-full border-gray-300 rounded-md shadow-sm"
                        onchange="this.form.submit()">
                        <option value="">Избери...</option>
                        <option value="month" {{ request('quick_period') == 'month' ? 'selected' : '' }}>Този месец
                        </option>
                        <option value="quarter" {{ request('quick_period') == 'quarter' ? 'selected' : '' }}>Това тримесечие
                        </option>
                        <option value="half_year" {{ request('quick_period') == 'half_year' ? 'selected' : '' }}>Шест месеца
                        </option>
                        <option value="year" {{ request('quick_period') == 'year' ? 'selected' : '' }}>Тази година
                        </option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md transition">
                        <i class="fas fa-search mr-1"></i> Филтрирай
                    </button>
                    <a href="{{ route('reports.kiosk-sales') }}"
                        class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition">
                        <i class="fas fa-undo mr-1"></i> Изчисти
                    </a>
                </div>
            </form>
        </div>

        <!-- Обобщение -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-lg p-4 shadow">
                <div class="text-sm text-indigo-600">Общ оборот</div>
                <div class="text-2xl font-bold text-indigo-700">{{ number_format($kioskSummary->sum('total_revenue'), 2) }}
                    €</div>
            </div>
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 shadow">
                <div class="text-sm text-blue-600">Брой продажби</div>
                <div class="text-2xl font-bold text-blue-700">{{ number_format($report->count()) }}</div>
            </div>
            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 shadow">
                <div class="text-sm text-green-600">Общо количество</div>
                <div class="text-2xl font-bold text-green-700">{{ number_format($report->sum('quantity'), 2) }}</div>
            </div>
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 shadow">
                <div class="text-sm text-purple-600">Средна стойност</div>
                <div class="text-2xl font-bold text-purple-700">
                    {{ $report->count() > 0 ? number_format($report->sum('total_price') / $report->count(), 2) : 0 }} €
                </div>
            </div>
        </div>

        <!-- Обобщение по щандове -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            @foreach ($kioskSummary as $kiosk)
                <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-lg p-4 shadow">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-lg font-semibold text-indigo-800">{{ $kiosk['kiosk_name'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">
                                <i class="fas fa-box"></i> {{ number_format($kiosk['items_count']) }} артикула
                            </div>
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-balance-scale"></i> {{ number_format($kiosk['total_quantity'], 2) }} бр.
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-indigo-700">{{ number_format($kiosk['total_revenue'], 2) }}
                                €</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Детайлен списък -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-semibold">Детайлен списък на продажбите</h2>
                <div class="text-sm text-gray-500">
                    {{ $report->count() }} записа
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Сметка</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Щанд</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Продукт</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">К-во</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ед. цена</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Общо</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($report as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-sm">{{ $item['session_token'] }}</td>
                                <td class="px-6 py-4 text-sm">{{ $item['date']->format('d.m.Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full text-xs">
                                        {{ $item['kiosk_name'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $item['product_name'] }}</td>
                                <td class="px-6 py-4 text-right">{{ number_format($item['quantity'], 3) }}
                                    {{ $item['unit'] }}</td>
                                <td class="px-6 py-4 text-right">{{ number_format($item['unit_price'], 2) }} €</td>
                                <td class="px-6 py-4 text-right font-bold">{{ number_format($item['total_price'], 2) }} €
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>Няма данни за избрания период</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="6" class="px-6 py-3 text-right font-bold">Общо:</td>
                            <td class="px-6 py-3 text-right font-bold text-indigo-600">
                                {{ number_format($report->sum('total_price'), 2) }} €
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
