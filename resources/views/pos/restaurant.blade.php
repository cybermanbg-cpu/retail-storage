{{-- resources/views/pos/restaurant.blade.php --}}
@extends('layouts.app')

@section('title', 'Restaurant POS - Продажби')

@section('hide_navigation', true)

@section('content')
    <div class="container-fluid px-3 md:px-4 py-3 h-screen flex flex-col">

        <!-- Top Bar - подобрен за таблет -->
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 bg-white p-3 md:p-4 rounded-2xl shadow flex-shrink-0">
            <div class="flex flex-wrap items-center gap-2 md:gap-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-utensils text-green-600 text-lg md:text-xl"></i>
                    <span class="font-bold text-sm md:text-base">Restaurant POS</span>
                    <div class="h-4 w-px bg-gray-300 hidden sm:block"></div>
                </div>
                <div class="flex items-center gap-2 text-xs md:text-sm text-gray-600">
                    <i class="fas fa-chair"></i>
                    <span>Маса <span id="tableNumber" class="font-semibold">12</span></span>
                    <button onclick="changeTable()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    <div class="h-4 w-px bg-gray-300 hidden sm:block"></div>
                </div>
                <div class="flex items-center gap-2 text-xs md:text-sm text-gray-600">
                    <i class="fas fa-warehouse"></i>
                    <span class="hidden sm:inline">{{ $storageObject->name }}</span>
                    <span class="sm:hidden">{{ Str::limit($storageObject->name, 15) }}</span>
                    <div class="h-4 w-px bg-gray-300 hidden sm:block"></div>
                </div>
                <div class="flex items-center gap-2 text-xs md:text-sm text-gray-600">
                    <i class="fas fa-user"></i>
                    <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                    <span class="sm:hidden">{{ Str::limit(Auth::user()->name, 10) }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-gray-500 hover:text-blue-600 p-2 rounded-lg transition"
                    title="Начало">
                    <i class="fas fa-home text-lg md:text-xl"></i>
                </a>
                <a href="{{ route('logout') }}" class="text-gray-500 hover:text-red-600 p-2 rounded-lg transition"
                    onclick="event.preventDefault(); document.getElementById('logout-form-restaurant').submit();"
                    title="Изход">
                    <i class="fas fa-sign-out-alt text-lg md:text-xl"></i>
                </a>
                <form id="logout-form-restaurant" action="{{ route('logout') }}" method="GET" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>

        <!-- Основно съдържание - responsive grid -->
        <div class="flex-1 grid grid-cols-12 gap-3 md:gap-4 min-h-0">

            <!-- Категории - на таблет стават 3 колони -->
            <div class="col-span-3 md:col-span-2 bg-white rounded-2xl shadow flex flex-col min-h-0">
                <div class="p-3 md:p-4 border-b flex-shrink-0">
                    <div class="flex justify-between items-center">
                        <h2 class="text-base md:text-xl font-bold">Категории</h2>
                        <button onclick="showAllProducts()" class="text-xs md:text-sm text-primary-600 hover:underline">
                            Всички
                        </button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-2 md:p-4 min-h-0">
                    <div class="grid grid-cols-2 gap-2 md:gap-3" id="categoriesGrid">
                        @foreach ($categories as $category)
                            <button
                                class="category-btn aspect-square flex flex-col items-center justify-center p-2 md:p-4 rounded-2xl transition-all {{ $loop->first ? 'bg-primary-600 text-white shadow-md' : 'bg-gray-100 hover:bg-gray-200' }}"
                                data-category-id="{{ $category->id }}" data-category-name="{{ $category->name }}">

                                <div class="text-3xl md:text-4xl mb-1 md:mb-2">
                                    {{ $category->icon ?? '📁' }}
                                </div>

                                <span
                                    class="font-medium text-center leading-tight text-xs md:text-sm">{{ Str::limit($category->name, 12) }}</span>

                                @if ($category->default_discount > 0)
                                    <span
                                        class="text-xs mt-1 bg-red-500 text-white px-1 md:px-2 py-0.5 rounded-full">-{{ $category->default_discount }}%</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Продукти - responsive grid -->
            <div class="col-span-9 md:col-span-7 bg-white rounded-2xl shadow flex flex-col min-h-0">
                <div class="p-3 md:p-4 border-b flex-shrink-0">
                    <div class="flex gap-2 md:gap-3">
                        <div class="flex-1 relative">
                            <i
                                class="fas fa-search absolute left-3 md:left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm md:text-base"></i>
                            <input type="text" id="searchInput"
                                class="w-full pl-9 md:pl-12 pr-10 md:pr-12 py-2 md:py-3 text-sm md:text-lg border-2 border-gray-200 rounded-2xl focus:border-primary-500 outline-none"
                                placeholder="🔍 Търси продукт...">
                            <button id="clearSearchBtn"
                                class="absolute right-2 md:right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 hidden">
                                <i class="fas fa-times-circle text-base md:text-lg"></i>
                            </button>
                        </div>
                        <button id="clearSearchBtnAlt"
                            class="px-3 md:px-5 py-2 md:py-3 bg-gray-200 rounded-2xl hover:bg-gray-300">
                            <i class="fas fa-times text-sm md:text-base"></i>
                        </button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-3 md:p-4 min-h-0">
                    <div id="productsGrid"
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2 md:gap-3 lg:gap-4">
                    </div>
                </div>
            </div>

            <!-- Количка - responsive -->
            <div class="col-span-12 md:col-span-3 bg-white rounded-2xl shadow flex flex-col min-h-0 md:min-h-full">
                <div class="p-3 md:p-4 border-b flex-shrink-0">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-xl md:text-2xl font-bold">Поръчка</h2>
                        <div class="text-xs md:text-sm text-gray-500"><span id="itemsCount">0</span> артикула</div>
                    </div>
                    <select id="clientSelect"
                        class="w-full mt-2 border-2 rounded-xl px-3 md:px-4 py-2 md:py-3 text-sm md:text-base">
                        <option value="">👤 Анонимен клиент</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">
                                {{ Str::limit($client->name, 20) }}{{ $client->phone ? ' (' . $client->phone . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="cartItems" class="flex-1 overflow-y-auto p-3 md:p-4 space-y-2 md:space-y-3 min-h-0">
                    <div class="text-center text-gray-400 py-8 md:py-12 text-sm md:text-base">Няма добавени продукти</div>
                </div>
                <div class="p-3 md:p-4 border-t bg-gray-50 rounded-b-2xl flex-shrink-0">
                    <div class="flex justify-between text-sm md:text-base mb-2">
                        <span class="text-gray-600">Междинна сума:</span>
                        <span id="subtotalAmount" class="font-semibold">0.00 €</span>
                    </div>
                    <div class="flex justify-between text-sm md:text-base text-red-600 mb-2">
                        <span>Отстъпка:</span>
                        <span id="discountAmount" class="font-semibold">0.00 €</span>
                    </div>
                    <div class="flex justify-between text-lg md:text-xl font-bold mb-3 md:mb-4 pt-2 border-t">
                        <span>Общо:</span>
                        <span id="totalAmount" class="text-primary-600">0.00 €</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 md:gap-3">
                        <button id="clearCartBtn"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 md:py-3 rounded-xl transition text-sm md:text-base">
                            <i class="fas fa-trash-alt mr-1 md:mr-2"></i> <span class="hidden sm:inline">Изчисти</span>
                        </button>
                        <button id="checkoutBtn"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 md:py-3 rounded-xl transition shadow-lg text-sm md:text-base">
                            <i class="fas fa-cash-register mr-1 md:mr-2"></i> <span
                                class="hidden sm:inline">Плащане</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модали - остават същите но с responsive класове -->
    <!-- Модал за избор на количество - responsive -->
    <div id="quantityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 md:top-32 mx-auto p-4 md:p-5 border w-[90%] sm:w-96 shadow-lg rounded-2xl bg-white">
            <h3 class="text-lg md:text-xl font-bold mb-4">Избор на количество</h3>
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-1">Продукт:</p>
                <p id="modalProductName" class="font-semibold text-base md:text-lg">-</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Количество (<span id="modalUnit">бр.</span>):</label>
                <input type="text" id="modalQuantity" value="1"
                    class="w-full text-center px-3 py-2 md:py-3 text-lg md:text-xl border-2 border-gray-300 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                <p class="text-xs text-gray-400 mt-2">Използвайте точка (.) или запетая (,) за дробни числа</p>
            </div>
            <div class="mb-4">
                <div class="grid grid-cols-4 gap-2 mb-3">
                    <button type="button" class="qty-preset bg-gray-100 hover:bg-gray-200 py-2 rounded-lg text-sm"
                        data-value="0.5">0.5</button>
                    <button type="button" class="qty-preset bg-gray-100 hover:bg-gray-200 py-2 rounded-lg text-sm"
                        data-value="1">1</button>
                    <button type="button" class="qty-preset bg-gray-100 hover:bg-gray-200 py-2 rounded-lg text-sm"
                        data-value="1.5">1.5</button>
                    <button type="button" class="qty-preset bg-gray-100 hover:bg-gray-200 py-2 rounded-lg text-sm"
                        data-value="2">2</button>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Единична цена:</label>
                <p id="modalUnitPrice" class="text-xl md:text-2xl font-bold text-primary-600">0.00 €</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Обща сума:</label>
                <p id="modalTotalPrice" class="text-2xl md:text-3xl font-bold text-green-600">0.00 €</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Наличност:</label>
                <p id="modalAvailable" class="text-sm md:text-md text-gray-600">-</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                <button id="confirmQuantityBtn"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 md:py-3 rounded-xl font-semibold text-sm md:text-base">
                    <i class="fas fa-check mr-2"></i> Добави
                </button>
                <button id="cancelQuantityBtn"
                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 md:py-3 rounded-xl font-semibold text-sm md:text-base">
                    <i class="fas fa-times mr-2"></i> Отказ
                </button>
            </div>
        </div>
    </div>

    <!-- Модал за плащане - responsive -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 md:top-20 mx-auto p-4 md:p-5 border w-[95%] sm:w-96 shadow-lg rounded-2xl bg-white">
            <div class="mt-2 md:mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg md:text-xl font-bold text-gray-900">Начин на плащане</h3>
                    <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-1">Обща сума за плащане:</p>
                    <p id="modalTotalAmount" class="text-2xl md:text-3xl font-bold text-primary-600">0.00 €</p>
                </div>
                <div class="space-y-3">
                    <button onclick="processPayment('card')"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 md:py-3 rounded-xl font-semibold transition text-sm md:text-base">
                        <i class="fas fa-credit-card mr-2"></i> Плащане с карта
                    </button>
                    <button onclick="showCashPaymentSection()"
                        class="w-full bg-green-600 hover:bg-green-700 text-white py-2 md:py-3 rounded-xl font-semibold transition text-sm md:text-base">
                        <i class="fas fa-money-bill mr-2"></i> Плащане в брой
                    </button>
                    <button onclick="closePaymentModal()"
                        class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 md:py-3 rounded-xl font-semibold transition text-sm md:text-base">
                        <i class="fas fa-arrow-left mr-2"></i> Върни се
                    </button>
                </div>
                <div id="cashPaymentSection" class="hidden mt-4 pt-4 border-t">
                    <label class="block text-sm font-medium mb-2">Въведете получена сума:</label>
                    <input type="text" id="cashAmount"
                        class="w-full px-3 py-2 border rounded-xl mb-3 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 text-base"
                        placeholder="0.00" onkeypress="handleCashAmountKeyPress(event)">
                    <p class="text-xs text-gray-400 mb-2">Използвайте точка (.) или запетая (,) за дробни числа</p>
                    <div id="changeInfo" class="hidden mb-3 p-2 bg-green-100 rounded-xl">
                        <span class="text-sm text-gray-700">Ресто:</span>
                        <span id="changeAmount" class="text-base md:text-lg font-bold text-green-600">0.00 €</span>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button onclick="confirmCashPayment()"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-xl font-semibold text-sm">
                            <i class="fas fa-check mr-2"></i> Потвърди
                        </button>
                        <button onclick="hideCashPaymentSection()"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-xl font-semibold text-sm">
                            <i class="fas fa-times mr-2"></i> Отказ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ⭐ ДИРЕКТНА ФУНКЦИЯ ЗА ИКОНИ В JS ⭐
        window.getProductIcon = function(productName) {
            const name = productName.toLowerCase();

            // Зеленчуци
            if (name.includes('домат')) return '🍅';
            if (name.includes('краставиц')) return '🥒';
            if (name.includes('пипер')) return '🫑';
            if (name.includes('чушк')) return '🫑';

            // Месо и риба
            if (name.includes('пилешк')) return '🍗';
            if (name.includes('месо')) return '🍖';
            if (name.includes('говежд')) return '🥩';
            if (name.includes('свинск')) return '🍖';
            if (name.includes('риба')) return '🐟';

            // Храна и напитки
            if (name.includes('кафе')) return '☕';
            if (name.includes('чай')) return '🍵';
            if (name.includes('хляб')) return '🍞';
            if (name.includes('сирен')) return '🧀';
            if (name.includes('кашкавал')) return '🧀';
            if (name.includes('салат')) return '🥗';
            if (name.includes('суп')) return '🥣';
            if (name.includes('десерт')) return '🍰';
            if (name.includes('сок')) return '🥤';
            if (name.includes('бира')) return '🍺';
            if (name.includes('вино')) return '🍷';
            if (name.includes('вода')) return '💧';
            if (name.includes('пица')) return '🍕';
            if (name.includes('паста')) return '🍝';

            // Плодове
            if (name.includes('ябълк')) return '🍎';
            if (name.includes('банан')) return '🍌';
            if (name.includes('портокал')) return '🍊';

            return '📦';
        };
    </script>

    <script src="{{ asset('js/restaurant-pos.js') }}"></script>

    <script>
        $(document).ready(function() {
            var cartItems = @json($currentCart->items ?? []);

            initRestaurantPOS(
                {{ $storageObject->id }},
                {{ $currentCart->id ?? 'null' }},
                cartItems
            );
        });
    </script>
@endpush
