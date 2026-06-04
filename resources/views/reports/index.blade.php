@extends('layouts.app')

@section('title', 'Доклади')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">📊 Доклади и анализи</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Executive Dashboard -->
        <a href="{{ route('reports.dashboard') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-blue-500 mb-3">📈</div>
            <h2 class="text-xl font-semibold mb-2">Executive Dashboard</h2>
            <p class="text-gray-600">Обобщени статистики и ключови показатели</p>
        </a>

        <!-- Обороти по продукти -->
        <a href="{{ route('reports.product-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-green-500 mb-3">📦</div>
            <h2 class="text-xl font-semibold mb-2">Обороти по продукти</h2>
            <p class="text-gray-600">Продажби, печалба и марж за всеки продукт</p>
        </a>

        <!-- Обороти по клиенти -->
        <a href="{{ route('reports.client-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-purple-500 mb-3">👥</div>
            <h2 class="text-xl font-semibold mb-2">Обороти по клиенти</h2>
            <p class="text-gray-600">Анализ на клиентската активност</p>
        </a>

        <!-- Обороти по касиери -->
        <a href="{{ route('reports.cashier-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-yellow-500 mb-3">🪪</div>
            <h2 class="text-xl font-semibold mb-2">Обороти по касиери</h2>
            <p class="text-gray-600">Ефективност на касиерите</p>
        </a>

        <!-- Обороти по служители -->
        <a href="{{ route('reports.staff-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-indigo-500 mb-3">👥</div>
            <h2 class="text-xl font-semibold mb-2">Оборот по служители</h2>
            <p class="text-gray-600">Ефективност на всички служители</p>
        </a>

        <!-- Месечни обороти -->
        <a href="{{ route('reports.monthly-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-red-500 mb-3">📅</div>
            <h2 class="text-xl font-semibold mb-2">Месечни обороти</h2>
            <p class="text-gray-600">Тенденции по месеци</p>
        </a>

        <!-- Доставки по продукти -->
        <a href="{{ route('reports.product-purchases') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-orange-500 mb-3">🚚</div>
            <h2 class="text-xl font-semibold mb-2">Доставки по продукти</h2>
            <p class="text-gray-600">Анализ на закупените количества</p>
        </a>

        <!-- Анализ на печалбата -->
        <a href="{{ route('reports.profit-analysis') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-teal-500 mb-3">💰</div>
            <h2 class="text-xl font-semibold mb-2">Анализ на печалбата</h2>
            <p class="text-gray-600">Себестойност (COGS) и печалба</p>
        </a>

        <!-- Складова наличност -->
        <a href="{{ route('reports.stock-status') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-cyan-500 mb-3">📦</div>
            <h2 class="text-xl font-semibold mb-2">Складова наличност</h2>
            <p class="text-gray-600">Текущо състояние на склада и стойност</p>
        </a>

        <!-- Продажби по часове -->
        <a href="{{ route('reports.hourly-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-pink-500 mb-3">⏰</div>
            <h2 class="text-xl font-semibold mb-2">Продажби по часове</h2>
            <p class="text-gray-600">Анализ на натовареността на касите</p>
        </a>

        <!-- Топ продукти -->
        <a href="{{ route('reports.top-products') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-amber-500 mb-3">🏆</div>
            <h2 class="text-xl font-semibold mb-2">Топ 10 продукти</h2>
            <p class="text-gray-600">Най-продаваните продукти</p>
        </a>

        <!-- Анализ на клиенти (RFM) -->
        <a href="{{ route('reports.customer-analysis') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-emerald-500 mb-3">👥</div>
            <h2 class="text-xl font-semibold mb-2">Анализ на клиенти</h2>
            <p class="text-gray-600">RFM анализ и класификация</p>
        </a>

        <!-- Дневен оборот -->
        <a href="{{ route('reports.daily-turnover') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-sky-500 mb-3">📅</div>
            <h2 class="text-xl font-semibold mb-2">Дневен оборот</h2>
            <p class="text-gray-600">Календарен изглед на продажбите</p>
        </a>

        <!-- Месечна печалба -->
        <a href="{{ route('reports.monthly-profit') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-lime-500 mb-3">📊</div>
            <h2 class="text-xl font-semibold mb-2">Месечна печалба</h2>
            <p class="text-gray-600">Приходи, разходи и печалба</p>
        </a>

        <!-- Нисък запас -->
        <a href="{{ route('reports.low-stock') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-rose-500 mb-3">⚠️</div>
            <h2 class="text-xl font-semibold mb-2">Сигнал за нисък запас</h2>
            <p class="text-gray-600">Продукти под минималното ниво</p>
        </a>

        <!-- Продажби по начини на плащане -->
        <a href="{{ route('reports.payment-methods') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-violet-500 mb-3">💳</div>
            <h2 class="text-xl font-semibold mb-2">Начини на плащане</h2>
            <p class="text-gray-600">Разпределение на продажбите</p>
        </a>

        <!-- ⭐ НОВИ SHOPPING MALL ДОКЛАДИ ⭐ -->

        <!-- Мол POS - Продажби -->
        <a href="{{ route('reports.shopping-mall-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-2 border-purple-200">
            <div class="text-4xl text-purple-500 mb-3">🛍️</div>
            <h2 class="text-xl font-semibold mb-2">Мол POS - Продажби</h2>
            <p class="text-gray-600">Обобщени продажби от мола (сметки)</p>
        </a>

        <!-- Мол POS - Продажби по щандове -->
        <a href="{{ route('reports.kiosk-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-2 border-indigo-200">
            <div class="text-4xl text-indigo-500 mb-3">🏪</div>
            <h2 class="text-xl font-semibold mb-2">Мол POS - Продажби по щандове</h2>
            <p class="text-gray-600">Анализ на продажбите от отделните щандове</p>
        </a>

        <!-- Мол POS - Продажби по продукти -->
        <a href="{{ route('reports.shopping-mall-product-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-2 border-fuchsia-200">
            <div class="text-4xl text-fuchsia-500 mb-3">🍕</div>
            <h2 class="text-xl font-semibold mb-2">Мол POS - Продукти</h2>
            <p class="text-gray-600">Най-продаваните продукти в мола</p>
        </a>
    </div>
</div>
@endsection