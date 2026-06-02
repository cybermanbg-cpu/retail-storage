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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- jQuery от CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    @stack('styles')
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Навигация -->
    <nav class="bg-white shadow-lg sticky top-0 z-40">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <i class="fas fa-boxes text-2xl text-primary-600"></i>
                        <span class="font-bold text-xl text-gray-800">Retail<span class="text-primary-600">Storage</span></span>
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-home mr-1"></i> Начало
                    </a>
                    <a href="{{ route('pos.index') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-cash-register mr-1"></i> POS
                    </a>
                    <!-- ⭐ НОВ ЛИНК КЪМ ДОКЛАДИ ⭐ -->
                    <a href="{{ route('reports.index') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-chart-line mr-1"></i> Доклади
                    </a>
                    <a href="{{ url('/admin') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-chalkboard-user mr-1"></i> Админ
                    </a>
                </div>

                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-gray-600 hover:text-gray-900 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
                
                <a href="{{ route('logout') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt mr-1"></i> Изход
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="GET" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                    <i class="fas fa-home mr-2"></i> Начало
                </a>
                <a href="{{ route('pos.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-green-500 hover:bg-green-600">
                    <i class="fas fa-cash-register mr-2"></i> POS
                </a>
                <!-- ⭐ МОБИЛЕН ЛИНК КЪМ ДОКЛАДИ ⭐ -->
                <a href="{{ route('reports.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                    <i class="fas fa-chart-line mr-2"></i> Доклади
                </a>
                <a href="{{ url('/admin') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                    <i class="fas fa-chalkboard-user mr-2"></i> Админ
                </a>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <!-- Футер - скрива се ако има секция 'hide_footer' -->
    @hasSection('hide_footer')
        <!-- Футерът е скрит -->
    @else
        <footer class="bg-gray-800 text-white mt-auto">
            <div class="container mx-auto px-4 py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Retail Storage System</h3>
                        <p class="text-gray-400 text-sm">Модерна система за управление на продажби и складови наличности</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Бързи връзки</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition">Начало</a></li>
                            <li><a href="{{ route('pos.index') }}" class="text-gray-400 hover:text-white transition">POS Система</a></li>
                            <li><a href="{{ route('reports.index') }}" class="text-gray-400 hover:text-white transition">Доклади</a></li>
                            <li><a href="{{ url('/admin') }}" class="text-gray-400 hover:text-white transition">Администрация</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Контакти</h3>
                        <p class="text-gray-400 text-sm">email: info@retailstorage.com</p>
                        <p class="text-gray-400 text-sm">тел: 0888 123 456</p>
                    </div>
                </div>
                <div class="border-t border-gray-700 mt-6 pt-6 text-center text-gray-400 text-sm">
                    <p>&copy; 2026 Retail Storage System. Всички права запазени.</p>
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