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
        
        @guest
            <!-- За нелогнати потребители - показваме предимства -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto mb-10">
                <div class="text-center">
                    <div class="text-5xl text-green-500 mb-3">⚡</div>
                    <h3 class="font-semibold text-gray-800">Бързи продажби</h3>
                    <p class="text-sm text-gray-500">Интуитивен интерфейс за бързо добавяне на продукти</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl text-blue-500 mb-3">📊</div>
                    <h3 class="font-semibold text-gray-800">Анализи и отчети</h3>
                    <p class="text-sm text-gray-500">Детайлни доклади за продажбите и печалбата</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl text-purple-500 mb-3">📦</div>
                    <h3 class="font-semibold text-gray-800">Складова наличност</h3>
                    <p class="text-sm text-gray-500">Проследяване на наличностите в реално време</p>
                </div>
            </div>
            
        
        @else
           
        @endguest
    </div>

    <!-- Статистики (само за логнати потребители) -->
    @auth
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-4xl text-green-500 mb-3">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($todaySales ?? 0, 2) }} €</div>
            <div class="text-gray-600">Продажби днес</div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition">
            <div class="text-4xl text-blue-500 mb-3">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($monthSales ?? 0, 2) }} €</div>
            <div class="text-gray-600">Продажби за месеца</div>
        </div>
    </div>

    <!-- Моят оборот -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-3">
            <h2 class="text-white font-semibold text-lg">
                <i class="fas fa-user mr-2"></i> Моят оборот
            </h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($myTodayTurnover, 2) }} €</div>
                    <div class="text-sm text-gray-600">Днес</div>
                </div>
                <div class="text-center p-4 bg-indigo-50 rounded-lg">
                    <div class="text-2xl font-bold text-indigo-600">{{ number_format($myWeekTurnover, 2) }} €</div>
                    <div class="text-sm text-gray-600">Тази седмица</div>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($myMonthTurnover, 2) }} €</div>
                    <div class="text-sm text-gray-600">Този месец</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Дневен оборот по потребители -->
    @if($dailyUserTurnovers->count() > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-3">
            <h2 class="text-white font-semibold text-lg">
                <i class="fas fa-users mr-2"></i> Дневен оборот по потребители
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Потребител</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Брой продажби</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Оборот днес</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $totalDailyTurnover = $dailyUserTurnovers->sum('today_turnover');
                    @endphp
                    @foreach($dailyUserTurnovers as $userTurnover)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $userTurnover['name'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $userTurnover['today_sales_count'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                            {{ number_format($userTurnover['today_turnover'], 2) }} €
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900">Общо</td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900">{{ $dailyUserTurnovers->sum('today_sales_count') }}</td>
                        <td class="px-6 py-3 text-sm font-semibold text-green-600">{{ number_format($totalDailyTurnover, 2) }} €</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    <!-- Общ брой продукти -->
    <div class="bg-white rounded-lg shadow-md p-6 text-center mb-8">
        <div class="text-4xl text-primary-600 mb-3">
            <i class="fas fa-boxes"></i>
        </div>
        <div class="text-3xl font-bold text-gray-800">{{ $totalProducts ?? 0 }}</div>
        <div class="text-gray-600">Активни продукта</div>
    </div>
    @endauth

    <!-- Функционалности за всички потребители -->
    <div class="mt-16 pt-8 border-t border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-8">Какво предлагаме</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-4xl mb-3">🖥️</div>
                <h3 class="font-semibold text-gray-800">POS Терминал</h3>
                <p class="text-sm text-gray-500">Бързо добавяне на продукти и финализиране на продажби</p>
            </div>
            <div class="text-center">
                <div class="text-4xl mb-3">🍽️</div>
                <h3 class="font-semibold text-gray-800">Ресторант POS</h3>
                <p class="text-sm text-gray-500">Специализиран режим за заведения с маси и категории</p>
            </div>
            <div class="text-center">
                <div class="text-4xl mb-3">🛍️</div>
                <h3 class="font-semibold text-gray-800">Мултищандова система</h3>
                <p class="text-sm text-gray-500">Няколко щанда, една сметка - удобство за клиентите</p>
            </div>
            <div class="text-center">
                <div class="text-4xl mb-3">📈</div>
                <h3 class="font-semibold text-gray-800">Детайлни отчети</h3>
                <p class="text-sm text-gray-500">Анализирайте продажбите и вземайте информирани решения</p>
            </div>
        </div>
    </div>
</div>
@endsection