@extends('layouts.app')

@section('title', 'Пазар POS - Продажби')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">🛍️ Продажби от Пазар POS</h1>
            <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
        </div>

        <!-- Филтри -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('reports.shopping-mall-sales') }}"
                class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">От дата</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">До дата</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500">
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
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md transition">
                        <i class="fas fa-search mr-1"></i> Филтрирай
                    </button>
                    <a href="{{ route('reports.shopping-mall-sales') }}"
                        class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition">
                        <i class="fas fa-undo mr-1"></i> Изчисти
                    </a>
                </div>
            </form>
        </div>

        <!-- Обобщение - данни за целия период -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 shadow">
                <div class="text-sm text-purple-600">Общ оборот</div>
                <div class="text-2xl font-bold text-purple-700">{{ number_format($totalRevenue, 2) }} €</div>
            </div>
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 shadow">
                <div class="text-sm text-blue-600">Брой сметки</div>
                <div class="text-2xl font-bold text-blue-700">{{ number_format($totalSessions) }}</div>
            </div>
            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 shadow">
                <div class="text-sm text-green-600">Завършени</div>
                <div class="text-2xl font-bold text-green-700">{{ number_format($totalCompleted, 2) }} €</div>
            </div>
            <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-lg p-4 shadow">
                <div class="text-sm text-red-600">Анулирани</div>
                <div class="text-2xl font-bold text-red-700">{{ number_format($totalCancelled, 2) }} €</div>
            </div>
        </div>

        <!-- Статистика по статуси -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Средна стойност на сметка</div>
                <div class="text-xl font-bold text-gray-800">
                    {{ $totalSessions > 0 ? number_format($totalRevenue / $totalSessions, 2) : 0 }} €
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Процент завършени</div>
                <div class="text-xl font-bold text-gray-800">
                    {{ $totalRevenue > 0 ? number_format(($totalCompleted / $totalRevenue) * 100, 1) : 0 }}%
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-green-500 h-2 rounded-full"
                        style="width: {{ $totalRevenue > 0 ? ($totalCompleted / $totalRevenue) * 100 : 0 }}%"></div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Процент анулирани</div>
                <div class="text-xl font-bold text-gray-800">
                    {{ $totalRevenue > 0 ? number_format(($totalCancelled / $totalRevenue) * 100, 1) : 0 }}%
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-red-500 h-2 rounded-full"
                        style="width: {{ $totalRevenue > 0 ? ($totalCancelled / $totalRevenue) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Таблица със сметки (с пагинация) -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-semibold">Списък на сметките</h2>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-list mr-1"></i> {{ $sessions->total() }} записа 
                    (страница {{ $sessions->currentPage() }} от {{ $sessions->lastPage() }})
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Токен</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Клиент</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Сума</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Създадена</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Платена</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Начин</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($sessions as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-sm font-bold">{{ $item['session_token'] }}</td>
                                <td class="px-6 py-4">{{ $item['customer_name'] }}</td>
                                <td class="px-6 py-4">
                                    @if ($item['status'] === 'completed')
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Завършена</span>
                                    @elseif($item['status'] === 'cancelled')
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">Анулирана</span>
                                    @else
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Активна</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-bold">{{ number_format($item['total_amount'], 2) }} €
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    {{ \Carbon\Carbon::parse($item['created_at'])->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    {{ $item['paid_at'] ? \Carbon\Carbon::parse($item['paid_at'])->format('d.m.Y H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($item['payment_method'] === 'cash')
                                        <span class="text-green-600">💵 В брой</span>
                                    @elseif($item['payment_method'] === 'card')
                                        <span class="text-blue-600">💳 Карта</span>
                                    @elseif($item['payment_method'] === 'bank_transfer')
                                        <span class="text-purple-600">🏦 Превод</span>
                                    @else
                                        -
                                    @endif
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
                        <tr class="border-t">
                            <td colspan="3" class="px-6 py-3 text-right font-semibold text-gray-600">
                                Общо за текущата страница:
                            </td>
                            <td class="px-6 py-3 text-right font-bold text-purple-600">
                                {{ number_format(collect($sessions->items())->sum('total_amount'), 2) }} €
                            </td>
                            <td colspan="3"></td>
                        </tr>
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="3" class="px-6 py-3 text-right text-lg">
                                ОБЩО ЗА ПЕРИОДА ({{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}):
                            </td>
                            <td class="px-6 py-3 text-right text-lg text-purple-700">
                                {{ number_format($totalRevenue, 2) }} €
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Пагинация -->
            @if ($sessions->hasPages())
                <div class="px-6 py-4 border-t bg-gray-50">
                    {{ $sessions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection