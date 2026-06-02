@extends('layouts.app')

@section('title', 'Доклади')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">📊 Доклади и анализи</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="{{ route('reports.dashboard') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-blue-500 mb-3">📈</div>
            <h2 class="text-xl font-semibold mb-2">Executive Dashboard</h2>
            <p class="text-gray-600">Обобщени статистики и ключови показатели</p>
        </a>
        
        <a href="{{ route('reports.product-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-green-500 mb-3">📦</div>
            <h2 class="text-xl font-semibold mb-2">Обороти по продукти</h2>
            <p class="text-gray-600">Продажби, печалба и марж за всеки продукт</p>
        </a>
        
        <a href="{{ route('reports.client-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-purple-500 mb-3">👥</div>
            <h2 class="text-xl font-semibold mb-2">Обороти по клиенти</h2>
            <p class="text-gray-600">Анализ на клиентската активност</p>
        </a>
        
        <a href="{{ route('reports.cashier-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-yellow-500 mb-3">🪪</div>
            <h2 class="text-xl font-semibold mb-2">Обороти по касиери</h2>
            <p class="text-gray-600">Ефективност на служителите</p>
        </a>
        
        <a href="{{ route('reports.monthly-sales') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-red-500 mb-3">📅</div>
            <h2 class="text-xl font-semibold mb-2">Месечни обороти</h2>
            <p class="text-gray-600">Тенденции по месеци</p>
        </a>
        
        <a href="{{ route('reports.product-purchases') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="text-4xl text-orange-500 mb-3">🚚</div>
            <h2 class="text-xl font-semibold mb-2">Доставки по продукти</h2>
            <p class="text-gray-600">Анализ на закупените количества</p>
        </a>
    </div>
</div>
@endsection