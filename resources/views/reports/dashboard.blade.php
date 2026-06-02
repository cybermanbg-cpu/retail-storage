@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('content')
    <div class="container mx-auto px-4 py-8">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">📈 Executive Dashboard</h1>
            <a href="{{ route('reports.index') }}" class="text-primary-600 hover:text-primary-800">← Назад към докладите</a>
        </div>


        <!-- KPI карти -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-3xl text-blue-500 mb-2">📦</div>
                <div class="text-2xl font-bold">{{ $totalProducts }}</div>
                <div class="text-gray-600">Активни продукти</div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-3xl text-green-500 mb-2">👥</div>
                <div class="text-2xl font-bold">{{ $totalClients }}</div>
                <div class="text-gray-600">Клиенти</div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-3xl text-yellow-500 mb-2">🪪</div>
                <div class="text-2xl font-bold">{{ $totalCashiers }}</div>
                <div class="text-gray-600">Касиери</div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-3xl text-purple-500 mb-2">💰</div>
                <div class="text-2xl font-bold text-green-600">{{ number_format($todaySales, 2) }} лв.</div>
                <div class="text-gray-600">Продажби днес ({{ $todayReceipts }} бр.)</div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-3xl text-indigo-500 mb-2">📅</div>
                <div class="text-2xl font-bold text-primary-600">{{ number_format($monthSales, 2) }} лв.</div>
                <div class="text-gray-600">Продажби този месец</div>
            </div>
        </div>

        <!-- Топ продукт и топ клиент -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">🏆 Най-продаван продукт за месеца</h2>
                @if ($topProduct)
                    <div class="text-center py-4">
                        <div class="text-2xl font-bold text-gray-800">{{ $topProduct->product_name_snapshot }}</div>
                        <div class="text-4xl font-bold text-green-600 mt-2">{{ number_format($topProduct->total_qty, 2) }}
                        </div>
                        <div class="text-gray-500">бройки продадени</div>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Няма данни за този период</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">🏆 Топ клиент за месеца</h2>
                @if ($topClient && $topClient->client)
                    <div class="text-center py-4">
                        <div class="text-2xl font-bold text-gray-800">{{ $topClient->client->name }}</div>
                        <div class="text-4xl font-bold text-green-600 mt-2">{{ number_format($topClient->total, 2) }} лв.
                        </div>
                        <div class="text-gray-500">оборот за месеца</div>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Няма данни за този период</p>
                @endif
            </div>
        </div>
    </div>
@endsection
