@extends('layouts.app')

@section('title', 'Начало - Retail Storage System')

@section('content')
<div class="container mx-auto px-4 py-12">
    <!-- Hero секция -->
    <div class="text-center mb-12">
        <h1 class="text-5xl font-bold text-gray-800 mb-4">
            Управление на продажбите
        </h1>
        <p class="text-xl text-gray-600 mb-8">
            Лесна и бърза POS система за вашия бизнес
        </p>
        <a href="{{ route('pos.index') }}" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white text-lg px-8 py-3 rounded-lg transition duration-300">
            <i class="fas fa-cash-register mr-2"></i>
            Стартирай POS
        </a>
    </div>

    <!-- Статистики (само за логнати потребители) -->
    @auth
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-4xl text-blue-500 mb-3">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800">{{ $totalProducts ?? 0 }}</div>
            <div class="text-gray-600">Активни продукта</div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-4xl text-green-500 mb-3">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($todaySales ?? 0, 2) }} €</div>
            <div class="text-gray-600">Продажби днес</div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-4xl text-purple-500 mb-3">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($totalSales ?? 0, 2) }} €</div>
            <div class="text-gray-600">Общо продажби</div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-4xl text-yellow-500 mb-3">
                <i class="fas fa-clock"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800">{{ $incompleteSales ?? 0 }}</div>
            <div class="text-gray-600">Незавършени продажби</div>
        </div>
    </div>

    <!-- Активни колички -->
    @if(isset($activeCarts) && $activeCarts->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Активни продажби в реално време</h2>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Касиер</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Обект</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Количка</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Брой артикули</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сума</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Последна активност</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($activeCarts as $cart)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $cart->cashier?->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $cart->storageObject?->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $cart->cart_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ count($cart->items ?? []) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-primary-600">
                            {{ number_format($cart->total_amount, 2) }} €
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $cart->updated_at->diffForHumans() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endauth

    <!-- Последни продукти (за всички) -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Последни добавени продукти</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($latestProducts as $product)
            <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition">
                <div class="font-semibold text-lg text-gray-800">{{ $product->name }}</div>
                <div class="text-sm text-gray-500">Артикул: {{ $product->sku }}</div>
                <div class="text-xl font-bold text-primary-600 mt-2">{{ number_format($product->base_price, 2) }} €</div>
                @if($product->has_variants)
                    <div class="text-xs text-gray-400 mt-1">✓ С варианти (цвят/размер)</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection