<!DOCTYPE html>
<html lang="bg">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Retail Storage System')</title>

    <!-- Vite CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- jQuery от CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    @stack('styles')
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Навигация - скрива се ако $hide_navigation е true -->
    @if (!isset($hide_navigation) && !$__env->yieldContent('hide_navigation'))
        <nav class="bg-white shadow-lg sticky top-0 z-40">
    <div class="container mx-auto px-3 sm:px-4 lg:px-6">
        <div class="flex justify-between items-center h-14 md:h-16">
            <!-- Лого -->
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <i class="fas fa-boxes text-xl md:text-2xl text-primary-600"></i>
                    <span class="font-bold text-base md:text-xl text-gray-800">Retail<span class="text-primary-600">Storage</span></span>
                </a>
            </div>

            <!-- Десктоп меню - скрива се на таблет -->
            <div class="hidden lg:flex items-center space-x-4">
                <a href="{{ route('home') }}"
                    class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition">
                    <i class="fas fa-home mr-1"></i> Начало
                </a>

                @auth
                    <a href="{{ route('pos.index') }}"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-cash-register mr-1"></i> POS
                    </a>
                    <a href="{{ route('restaurant.pos') }}"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-utensils mr-1"></i> Ресторант
                    </a>
                    <a href="{{ route('shopping-mall.pos') }}"
                        class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-cash-register mr-1"></i> Мол (Каса)
                    </a>
                    <a href="{{ route('shopping-mall.kiosk') }}"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-store mr-1"></i> Мол (Щанд)
                    </a>
                    <a href="{{ route('reports.index') }}"
                        class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-chart-line mr-1"></i> Доклади
                    </a>
                    <a href="{{ url('/admin') }}"
                        class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-chalkboard-user mr-1"></i> Админ
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-cash-register mr-1"></i> POS
                    </a>
                    <a href="{{ route('login') }}"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-utensils mr-1"></i> Ресторант
                    </a>
                    <a href="{{ route('login') }}"
                        class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-cash-register mr-1"></i> Мол (Каса)
                    </a>
                    <a href="{{ route('login') }}"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-store mr-1"></i> Мол (Щанд)
                    </a>
                    <a href="{{ route('login') }}"
                        class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-chart-line mr-1"></i> Доклади
                    </a>
                    <a href="{{ route('login') }}"
                        class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-chalkboard-user mr-1"></i> Админ
                    </a>
                @endauth
            </div>

            <!-- Таблет меню - вижда се само на таблет -->
            <div class="hidden md:flex lg:hidden items-center space-x-2">
                <a href="{{ route('home') }}"
                    class="text-gray-700 hover:text-primary-600 p-2 rounded-md transition">
                    <i class="fas fa-home text-lg"></i>
                </a>
                @auth
                    <a href="{{ route('pos.index') }}"
                        class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg transition">
                        <i class="fas fa-cash-register text-lg"></i>
                    </a>
                    <a href="{{ route('restaurant.pos') }}"
                        class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg transition">
                        <i class="fas fa-utensils text-lg"></i>
                    </a>
                    <a href="{{ route('reports.index') }}"
                        class="text-gray-700 hover:text-primary-600 p-2 rounded-md transition">
                        <i class="fas fa-chart-line text-lg"></i>
                    </a>
                    <a href="{{ url('/admin') }}"
                        class="text-gray-700 hover:text-primary-600 p-2 rounded-md transition">
                        <i class="fas fa-chalkboard-user text-lg"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg transition">
                        <i class="fas fa-cash-register text-lg"></i>
                    </a>
                    <a href="{{ route('login') }}"
                        class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg transition">
                        <i class="fas fa-utensils text-lg"></i>
                    </a>
                    <a href="{{ route('login') }}"
                        class="text-gray-700 hover:text-primary-600 p-2 rounded-md transition">
                        <i class="fas fa-chart-line text-lg"></i>
                    </a>
                    <a href="{{ route('login') }}"
                        class="text-gray-700 hover:text-primary-600 p-2 rounded-md transition">
                        <i class="fas fa-chalkboard-user text-lg"></i>
                    </a>
                @endauth
            </div>

            <!-- Мобилно меню (бургер) -->
            <div class="flex items-center gap-2">
                <!-- Вход / Изход бутон (мобилен) -->
                @auth
                    <a href="{{ route('logout') }}"
                        class="md:hidden text-gray-700 hover:text-primary-600 px-2 py-2 rounded-md text-sm font-medium transition"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt text-lg"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="md:hidden bg-primary-600 hover:bg-primary-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-sign-in-alt mr-1"></i> Вход
                    </a>
                @endauth
                
                <button id="mobile-menu-button" class="md:hidden text-gray-600 hover:text-gray-900 focus:outline-none p-2">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Мобилно меню - изскачащо -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t shadow-lg">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="{{ route('home') }}"
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                <i class="fas fa-home mr-2"></i> Начало
            </a>

            @auth
                <a href="{{ route('pos.index') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-white bg-green-500 hover:bg-green-600">
                    <i class="fas fa-cash-register mr-2"></i> POS
                </a>
                <a href="{{ route('restaurant.pos') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-white bg-green-500 hover:bg-green-600">
                    <i class="fas fa-utensils mr-2"></i> Ресторант POS
                </a>
                <a href="{{ route('shopping-mall.pos') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-white bg-purple-500 hover:bg-purple-600">
                    <i class="fas fa-cash-register mr-2"></i> Мол (Каса)
                </a>
                <a href="{{ route('shopping-mall.kiosk') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-white bg-indigo-500 hover:bg-indigo-600">
                    <i class="fas fa-store mr-2"></i> Мол (Щанд)
                </a>
                <a href="{{ route('reports.index') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                    <i class="fas fa-chart-line mr-2"></i> Доклади
                </a>
                <a href="{{ url('/admin') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                    <i class="fas fa-chalkboard-user mr-2"></i> Админ
                </a>
                <a href="{{ route('logout') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-white bg-red-500 hover:bg-red-600"
                    onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
                    <i class="fas fa-sign-out-alt mr-2"></i> Изход
                </a>
                <form id="logout-form-mobile" action="{{ route('logout') }}" method="GET" style="display: none;">
                    @csrf
                </form>
            @else
                <a href="{{ route('login') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-white bg-green-500 hover:bg-green-600">
                    <i class="fas fa-cash-register mr-2"></i> POS
                </a>
                <a href="{{ route('login') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-white bg-green-500 hover:bg-green-600">
                    <i class="fas fa-utensils mr-2"></i> Ресторант POS
                </a>
                <a href="{{ route('login') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-white bg-purple-500 hover:bg-purple-600">
                    <i class="fas fa-cash-register mr-2"></i> Мол (Каса)
                </a>
                <a href="{{ route('login') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-white bg-indigo-500 hover:bg-indigo-600">
                    <i class="fas fa-store mr-2"></i> Мол (Щанд)
                </a>
                <a href="{{ route('login') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                    <i class="fas fa-chart-line mr-2"></i> Доклади
                </a>
                <a href="{{ route('login') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                    <i class="fas fa-chalkboard-user mr-2"></i> Админ
                </a>
            @endauth
        </div>
    </div>
</nav>
    @endif

    <!-- ⭐ ТУК СЕКЦИЯ ЗА ФЛАШ СЪОБЩЕНИЯ ⭐ -->
    @if (session('success'))
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md flex justify-between items-center"
                role="alert">
                <div>
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md flex justify-between items-center"
                role="alert">
                <div>
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ session('error') }}
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded shadow-md flex justify-between items-center"
                role="alert">
                <div>
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('warning') }}
                </div>
                <button onclick="this.parentElement.remove()" class="text-yellow-700 hover:text-yellow-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md">
                <div class="font-bold mb-2">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Моля, поправете следните грешки:
                </div>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <!-- Футер - скрива се ако има секция 'hide_footer' -->
    @hasSection('hide_footer')
        <!-- Футерът е скрит -->
    @else
        <footer class="bg-gray-800 text-white mt-auto py-6 border-t border-gray-700">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-center gap-3 text-sm">
                    <div class="text-gray-400">
                        <i class="fas fa-code-branch mr-1"></i> Идеен проект и създател
                    </div>
                    <div class="font-medium text-gray-300">
                        Радослав Радулов
                    </div>
                    <div>
                        <a href="mailto:rado.radulov@me.com" class="text-gray-400 hover:text-white transition">
                            <i class="fas fa-envelope mr-1"></i> rado.radulov@me.com
                        </a>
                    </div>
                </div>
                <div class="text-center text-xs text-gray-500 mt-4 pt-3 border-t border-gray-700">
                    &copy; 2026 Retail Storage System. Всички права запазени.
                </div>
            </div>
        </footer>
    @endif

    <script>
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>

    @stack('scripts')
</body>

</html>
