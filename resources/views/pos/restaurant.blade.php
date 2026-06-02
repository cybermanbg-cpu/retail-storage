@extends('layouts.app')

@section('title', 'Restaurant POS - Продажби')

@section('hide_navigation', true)
@section('content')
    <div class="container-fluid px-4 py-3 h-screen flex flex-col">

        <!-- Top Bar (фиксиран) -->
        <div class="flex justify-between items-center mb-4 bg-white p-4 rounded-2xl shadow flex-shrink-0">
            <div class="flex items-center justify-between mb-3 bg-white p-2 rounded-lg shadow-sm text-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-utensils text-green-600"></i>
                    <span class="font-bold">Restaurant POS</span>
                    <div class="h-4 w-px bg-gray-300"></div>
                    <div class="text-gray-600">
                        Маса <span id="tableNumber" class="font-semibold">12</span>
                        <button onclick="changeTable()" class="text-gray-400 hover:text-gray-600 ml-1">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                    </div>
                    <div class="h-4 w-px bg-gray-300"></div>
                    <div class="text-gray-600">
                        📍 {{ $storageObject->name }}
                    </div>
                    <div class="h-4 w-px bg-gray-300"></div>
                    <div class="text-gray-600">
                        👤 {{ Auth::user()->name }}
                    </div>
                </div>
        </div>
    </div>

    <!-- Основно съдържание (скролиращо) -->
    <div class="flex-1 grid grid-cols-12 gap-4 min-h-0">

        <!-- Категории (фиксирана ширина, скролиращо съдържание) -->
        <div class="col-span-2 bg-white rounded-2xl shadow flex flex-col min-h-0">
            <div class="p-4 border-b flex-shrink-0">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold">Категории</h2>
                    <button onclick="showAllProducts()" class="text-sm text-primary-600 hover:underline">
                        Всички
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4 min-h-0">
                <div class="grid grid-cols-2 gap-3" id="categoriesGrid">
                    @foreach ($categories as $category)
                        <button
                            class="category-btn aspect-square flex flex-col items-center justify-center p-4 rounded-2xl transition-all {{ $loop->first ? 'bg-primary-600 text-white shadow-md' : 'bg-gray-100 hover:bg-gray-200' }}"
                            data-category-id="{{ $category->id }}" data-category-name="{{ $category->name }}">
                            <i class="{{ $category->icon ?? 'fas fa-tag' }} text-3xl mb-2"></i>
                            <span class="font-medium text-center leading-tight text-sm">{{ $category->name }}</span>
                            @if ($category->default_discount > 0)
                                <span
                                    class="text-xs mt-1 bg-red-500 text-white px-2 py-0.5 rounded-full">-{{ $category->default_discount }}%</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Продукти -->
        <div class="col-span-7 bg-white rounded-2xl shadow flex flex-col min-h-0">
            <div class="p-4 border-b flex-shrink-0">
                <div class="flex gap-3">
                    <input type="text" id="searchInput"
                        class="flex-1 px-5 py-3 text-lg border-2 border-gray-200 rounded-2xl focus:border-primary-500 outline-none"
                        placeholder="🔍 Търси продукт по име...">
                    <button id="clearSearchBtn" class="px-5 py-3 bg-gray-200 rounded-2xl hover:bg-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4 min-h-0">
                <div id="productsGrid" class="grid grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
                    <!-- Products loaded by JS -->
                </div>
            </div>
        </div>

        <!-- Количка -->
        <div class="col-span-3 bg-white rounded-2xl shadow flex flex-col min-h-0">
            <!-- Заглавна част (фиксирана) -->
            <div class="p-4 border-b flex-shrink-0">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-2xl font-bold">Поръчка</h2>
                    <div class="text-sm text-gray-500">
                        <span id="itemsCount">0</span> артикула
                    </div>
                </div>
                <select id="clientSelect" class="w-full mt-2 border-2 rounded-xl px-4 py-3 text-base">
                    <option value="">👤 Анонимен клиент</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}
                            {{ $client->phone ? '(' . $client->phone . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Списък с артикули (скролиращ) -->
            <div id="cartItems" class="flex-1 overflow-y-auto p-4 space-y-3 min-h-0">
                <div class="text-center text-gray-400 py-12">Няма добавени продукти</div>
            </div>

            <!-- Обща сума и бутони (фиксирана) -->
            <div class="p-4 border-t bg-gray-50 rounded-b-2xl flex-shrink-0">
                <div class="flex justify-between text-base mb-2">
                    <span class="text-gray-600">Междинна сума:</span>
                    <span id="subtotalAmount" class="font-semibold">0.00 €</span>
                </div>
                <div class="flex justify-between text-base text-red-600 mb-2">
                    <span>Отстъпка:</span>
                    <span id="discountAmount" class="font-semibold">0.00 €</span>
                </div>
                <div class="flex justify-between text-xl font-bold mb-4 pt-2 border-t">
                    <span>Общо:</span>
                    <span id="totalAmount" class="text-primary-600">0.00 €</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button id="clearCartBtn"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 rounded-xl transition">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Изчисти
                    </button>
                    <button id="checkoutBtn"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition shadow-lg">
                        <i class="fas fa-cash-register mr-2"></i>
                        Плащане
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Модал за плащане -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-2xl bg-white">
            <h3 class="text-xl font-bold mb-4">Начин на плащане</h3>
            <div class="mb-4">
                <p class="text-gray-600">Обща сума:</p>
                <p id="modalTotalAmount" class="text-3xl font-bold text-primary-600">0.00 €</p>
            </div>
            <div class="space-y-3">
                <button onclick="processPayment('card')"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl">
                    <i class="fas fa-credit-card mr-2"></i> Карта
                </button>
                <button onclick="processPayment('cash')"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl">
                    <i class="fas fa-money-bill mr-2"></i> В брой
                </button>
                <button onclick="closePaymentModal()"
                    class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 rounded-xl">
                    Отказ
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentCategoryId = null;
        let currentItems = [];
        let storageObjectId = {{ $storageObject->id }};
        let tableNumber = localStorage.getItem('tableNumber') || 12;

        $('#tableNumber').text(tableNumber);

        function changeTable() {
            let newTable = prompt('Въведете номер на маса:', tableNumber);
            if (newTable && !isNaN(newTable)) {
                tableNumber = parseInt(newTable);
                $('#tableNumber').text(tableNumber);
                localStorage.setItem('tableNumber', tableNumber);
            }
        }

        // Зареждане на продукти
        function loadProducts(categoryId) {
            $.get(`/pos/products-by-category/${categoryId}`)
                .done(function(products) {
                    displayProducts(products);
                });
        }

        function showAllProducts() {
            $.get('/pos/all-products')
                .done(function(products) {
                    displayProducts(products);
                    $('.category-btn').removeClass('bg-primary-600 text-white shadow-md').addClass('bg-gray-100');
                });
        }

        function displayProducts(products) {
            let html = '';
            if (products.length === 0) {
                html = '<div class="col-span-full text-center text-gray-400 py-12">Няма продукти в тази категория</div>';
            } else {
                products.forEach(p => {
                    html += `
                <div class="product-card bg-white border-2 border-gray-100 hover:border-primary-500 rounded-3xl p-4 cursor-pointer transition-all active:scale-95 text-center"
                     data-product-id="${p.id}"
                     data-product-name="${p.name.replace(/'/g, "\\'")}"
                     data-price="${p.discounted_price}"
                     data-original-price="${p.base_price}"
                     data-discount="${p.discount_percent}">
                    <div class="text-5xl mb-3">${getProductIcon(p.name)}</div>
                    <div class="font-semibold text-lg leading-tight mb-1">${p.name}</div>
                    <div class="text-2xl font-bold text-primary-600">${parseFloat(p.discounted_price).toFixed(2)} €</div>
                    ${p.discount_percent > 0 ? `<div class="text-sm text-red-500 line-through">${parseFloat(p.base_price).toFixed(2)} €</div>` : ''}
                    ${p.discount_percent > 0 ? `<div class="text-xs text-green-600">-${p.discount_percent}%</div>` : ''}
                </div>
            `;
                });
            }
            $('#productsGrid').html(html);
            attachProductEvents();
        }

        function getProductIcon(name) {
            const icons = {
                'пица': '🍕',
                'домати': '🍅',
                'салата': '🥗',
                'супа': '🥣',
                'месо': '🍖',
                'риба': '🐟',
                'десерт': '🍰',
                'кафе': '☕',
                'чай': '🍵',
                'вода': '💧',
                'сок': '🥤',
                'бира': '🍺',
                'вино': '🍷'
            };
            for (let [key, icon] of Object.entries(icons)) {
                if (name.toLowerCase().includes(key)) return icon;
            }
            return '🍽️';
        }

        function attachProductEvents() {
            $('.product-card').off('click').on('click', function() {
                const product = {
                    id: $(this).data('product-id'),
                    name: $(this).data('product-name'),
                    price: parseFloat($(this).data('price')),
                    original_price: parseFloat($(this).data('original-price')),
                    discount: parseFloat($(this).data('discount'))
                };
                addToCart(product);
            });
        }

        function addToCart(product) {
            let existing = currentItems.find(item => item.product_id === product.id);

            if (existing) {
                existing.quantity++;
            } else {
                currentItems.push({
                    product_id: product.id,
                    product_name: product.name,
                    price: product.price,
                    original_price: product.original_price,
                    discount: product.discount,
                    quantity: 1
                });
            }
            updateCart();
        }

        function updateCart() {
            let html = '';
            let subtotal = 0;
            let totalDiscount = 0;
            let itemsCount = 0;

            currentItems.forEach((item, i) => {
                const itemTotal = item.quantity * item.price;
                const originalTotal = item.quantity * item.original_price;
                const discount = originalTotal - itemTotal;

                subtotal += originalTotal;
                totalDiscount += discount;
                itemsCount += item.quantity;

                html += `
            <div class="bg-gray-50 rounded-2xl p-4 hover:shadow transition">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="font-semibold text-lg">${item.product_name}</div>
                        <div class="text-sm text-gray-500">${item.quantity} × ${item.price.toFixed(2)} €</div>
                        ${item.discount > 0 ? `<div class="text-xs text-green-600">-${item.discount}% отстъпка</div>` : ''}
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-xl text-primary-600">${itemTotal.toFixed(2)} €</div>
                        <div class="flex gap-2 mt-2 justify-end">
                            <button onclick="changeQuantity(${i}, -1)" class="w-8 h-8 bg-white rounded-xl border-2 flex items-center justify-center text-lg hover:bg-gray-100">-</button>
                            <button onclick="changeQuantity(${i}, 1)" class="w-8 h-8 bg-white rounded-xl border-2 flex items-center justify-center text-lg hover:bg-gray-100">+</button>
                            <button onclick="removeFromCart(${i})" class="ml-1 text-red-500 w-8 h-8 rounded-xl hover:bg-red-50">✕</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
            });

            $('#cartItems').html(html || '<div class="text-center text-gray-400 py-12">Няма добавени продукти</div>');
            $('#subtotalAmount').text(subtotal.toFixed(2) + ' €');
            $('#discountAmount').text(totalDiscount.toFixed(2) + ' €');
            $('#totalAmount').text((subtotal - totalDiscount).toFixed(2) + ' €');
            $('#itemsCount').text(itemsCount);
        }

        window.changeQuantity = function(index, change) {
            currentItems[index].quantity += change;
            if (currentItems[index].quantity < 1) currentItems.splice(index, 1);
            updateCart();
        };

        window.removeFromCart = function(index) {
            currentItems.splice(index, 1);
            updateCart();
        };

        // Category buttons
        $('.category-btn').click(function() {
            currentCategoryId = $(this).data('category-id');
            $('.category-btn').removeClass('bg-primary-600 text-white shadow-md').addClass('bg-gray-100');
            $(this).addClass('bg-primary-600 text-white shadow-md');
            loadProducts(currentCategoryId);
            $('#searchInput').val('');
        });

        // Изчистване на количката
        $('#clearCartBtn').click(function() {
            if (currentItems.length > 0 && confirm('Сигурни ли сте, че искате да изчистите цялата поръчка?')) {
                currentItems = [];
                updateCart();
            }
        });

        // Плащане
        function openPaymentModal() {
            if (currentItems.length === 0) {
                alert('Няма продукти в поръчката!');
                return;
            }
            let total = parseFloat($('#totalAmount').text());
            $('#modalTotalAmount').text(total.toFixed(2) + ' €');
            $('#paymentModal').removeClass('hidden');
        }

        function closePaymentModal() {
            $('#paymentModal').addClass('hidden');
        }

        function processPayment(method) {
            let clientId = $('#clientSelect').val();

            $.ajax({
                url: '/pos/restaurant-receipt',
                method: 'POST',
                data: {
                    client_id: clientId,
                    storage_object_id: storageObjectId,
                    items: currentItems.map(item => ({
                        product_id: item.product_id,
                        quantity: item.quantity,
                        price: item.price
                    })),
                    table_number: tableNumber,
                    payment_method: method,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.success) {
                        alert(
                            `✅ Поръчка №${res.receipt_number} на маса ${tableNumber} е записана успешно!\nПлащане: ${method === 'card' ? 'Карта' : 'В брой'}`
                        );
                        currentItems = [];
                        updateCart();
                        closePaymentModal();
                    } else {
                        alert('❌ Грешка: ' + res.message);
                    }
                },
                error: function(xhr) {
                    alert('Възникна грешка!');
                }
            });
        }

        $('#checkoutBtn').click(openPaymentModal);

        // Търсене
        let searchTimeout;
        $('#searchInput').on('input', function() {
            clearTimeout(searchTimeout);
            let search = $(this).val();

            if (search.length < 2) {
                if (currentCategoryId) loadProducts(currentCategoryId);
                return;
            }

            searchTimeout = setTimeout(() => {
                $.get(
                        `/pos/search-products?search=${encodeURIComponent(search)}&category_id=${currentCategoryId || ''}`
                    )
                    .done(function(products) {
                        displayProducts(products);
                    });
            }, 300);
        });

        $('#clearSearchBtn').click(function() {
            $('#searchInput').val('');
            if (currentCategoryId) loadProducts(currentCategoryId);
            else showAllProducts();
        });

        // Инициализация
        if ($('.category-btn').first().length) {
            $('.category-btn').first().click();
        } else {
            showAllProducts();
        }
    </script>
@endpush
