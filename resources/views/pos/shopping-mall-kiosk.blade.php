{{-- resources/views/pos/shopping-mall-kiosk.blade.php --}}
@extends('layouts.app')

@section('title', 'Щанд - Shopping Mall POS')

@section('hide_navigation', true)

@section('content')
    <div class="container-fluid px-4 py-3 h-screen flex flex-col">

        <!-- Header за щанд (като касата, но със зелено) -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-2xl shadow-lg p-4 mb-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-store text-2xl"></i>
                        <h1 class="text-xl font-bold">Щанд: {{ Auth::user()->name }}</h1>
                        <span class="text-xs bg-white/20 px-2 py-1 rounded-full">
                            <i class="fas fa-plus-circle"></i> Добавяне
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-green-200">
                        <span><i class="fas fa-building"></i> {{ $owner?->name ?? '-' }}</span>
                        <span><i class="fas fa-warehouse"></i> {{ $storageObject?->name ?? '-' }}</span>
                        <span><i class="fas fa-clock"></i> {{ now()->format('H:i') }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="openCreateSessionModal()"
                        class="bg-white text-green-700 hover:bg-green-50 px-4 py-2 rounded-xl text-sm font-semibold transition shadow-md">
                        <i class="fas fa-plus mr-1"></i> Сметка
                    </button>
                    <a href="{{ route('shopping-mall.pos') }}"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition shadow-md">
                        <i class="fas fa-exchange-alt mr-1"></i> Към Каса
                    </a>
                    <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="bg-red-500/80 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
                        <i class="fas fa-sign-out-alt mr-1"></i> Изход
                    </button>
                    <form id="logout-form" action="{{ route('logout') }}" method="GET" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>

        <div class="flex-1 grid grid-cols-12 gap-4 min-h-0">

            <!-- Ляв панел - Активни сметки -->
            <div class="col-span-4 bg-white rounded-2xl shadow flex flex-col min-h-0">
                <div class="p-4 border-b flex-shrink-0">
                    <h2 class="text-xl font-bold">Активни сметки</h2>
                    <p class="text-sm text-gray-500" id="sessionsCount">0 отворени</p>
                </div>
                <div id="sessionsList" class="flex-1 overflow-y-auto p-4 space-y-3 min-h-0">
                    <div class="text-center text-gray-400 py-12">
                        <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                        <p>Зареждане на сметки...</p>
                    </div>
                </div>
            </div>

            <!-- Централен панел - Продукти -->
            <div class="col-span-5 bg-white rounded-2xl shadow flex flex-col min-h-0">
                <div class="p-4 border-b flex-shrink-0">
                    <input type="text" id="searchProducts" class="w-full px-4 py-3 border-2 rounded-xl"
                        placeholder="Търси продукт...">
                </div>
                <div id="productsGrid" class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($products as $product)
                            @php
                                $availableQty = $product['available_qty'] ?? 0;
                                $stockClass = $availableQty <= 0 ? 'opacity-60 grayscale' : '';
                                $stockBadge = '';
                                if ($availableQty <= 0) {
                                    $stockBadge = '<div class="text-xs text-red-500 mt-1">✗ Няма наличност</div>';
                                } elseif ($availableQty < 5) {
                                    $stockBadge = '<div class="text-xs text-orange-500 mt-1">⚠️ Остава: ' . $availableQty . '</div>';
                                } else {
                                    $stockBadge = '<div class="text-xs text-green-500 mt-1">✓ Налично: ' . $availableQty . '</div>';
                                }
                            @endphp
                            <div class="product-card bg-white border-2 border-gray-100 hover:border-green-500 rounded-2xl p-3 cursor-pointer transition-all hover:shadow-lg text-center {{ $stockClass }}"
                                data-product-id="{{ $product['id'] }}"
                                data-product-name="{{ addslashes($product['name']) }}"
                                data-product-price="{{ $product['base_price'] }}"
                                data-product-unit="{{ $product['unit_symbol'] }}"
                                data-available-qty="{{ $availableQty }}"
                                onclick="addToCurrentSession(this)">
                                <div class="text-5xl mb-2">{{ getProductIcon($product['name']) }}</div>
                                <div class="font-semibold text-sm leading-tight">{{ Str::limit($product['name'], 30) }}</div>
                                <div class="text-green-600 font-bold mt-2">{{ number_format($product['base_price'], 2) }} €</div>
                                <div class="text-xs text-gray-400 mt-1">{{ $product['unit_symbol'] }}</div>
                                {!! $stockBadge !!}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Десен панел - Текуща сметка -->
            <div class="col-span-3 bg-white rounded-2xl shadow flex flex-col min-h-0">
                <div id="sessionInfo" class="p-4 border-b">
                    <div class="text-center text-gray-400 py-8">
                        <i class="fas fa-folder-open text-5xl mb-3"></i>
                        <p>Изберете сметка</p>
                    </div>
                </div>
                <div id="sessionItems" class="flex-1 overflow-y-auto p-4"></div>
                <div id="sessionActions" class="p-4 border-t bg-gray-50 hidden">
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-bold">Общо:</span>
                        <span id="sessionTotal" class="text-2xl font-bold text-green-600">0.00 €</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <button onclick="openNoteModal()" class="bg-gray-300 hover:bg-gray-400 py-2 rounded-xl">
                            <i class="fas fa-sticky-note mr-1"></i> Бележка
                        </button>
                        <button onclick="cancelCurrentSession()" class="bg-red-500 hover:bg-red-600 text-white py-2 rounded-xl">
                            <i class="fas fa-trash mr-1"></i> Анулирай
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модали (същите като в касата) -->
    <div id="createSessionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Нова сметка</h3>
            <input type="text" id="customerName" class="w-full border rounded-xl p-2 mb-3" placeholder="Име на клиент">
            <input type="text" id="customerPhone" class="w-full border rounded-xl p-2 mb-3" placeholder="Телефон">
            <textarea id="sessionNote" class="w-full border rounded-xl p-2 mb-3" rows="2" placeholder="Бележка"></textarea>
            <div class="flex gap-2">
                <button onclick="createSession()" class="flex-1 bg-green-600 text-white py-2 rounded-xl">Създай</button>
                <button onclick="closeCreateSessionModal()" class="flex-1 bg-gray-300 py-2 rounded-xl">Отказ</button>
            </div>
        </div>
    </div>

    <div id="quantityModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Количество</h3>
            <p id="modalProductName" class="mb-3 font-semibold"></p>
            <div class="mb-2">
                <p class="text-sm text-gray-500">Наличност: <span id="modalAvailableQty" class="font-semibold text-green-600">0</span></p>
            </div>
            <input type="text" id="modalQuantity" value="1" class="w-full text-center text-2xl border rounded-xl p-3 mb-3">
            <div class="grid grid-cols-4 gap-2 mb-4">
                <button onclick="setQuantity(0.5)" class="bg-gray-100 py-2 rounded">0.5</button>
                <button onclick="setQuantity(1)" class="bg-gray-100 py-2 rounded">1</button>
                <button onclick="setQuantity(2)" class="bg-gray-100 py-2 rounded">2</button>
                <button onclick="setQuantity(3)" class="bg-gray-100 py-2 rounded">3</button>
            </div>
            <div class="flex gap-2">
                <button onclick="confirmAddToSession()" class="flex-1 bg-green-600 text-white py-2 rounded-xl">Добави</button>
                <button onclick="closeQuantityModal()" class="flex-1 bg-gray-300 py-2 rounded-xl">Отказ</button>
            </div>
        </div>
    </div>

    <div id="noteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Бележка</h3>
            <textarea id="noteText" class="w-full border rounded-xl p-2 mb-3" rows="3"></textarea>
            <div class="flex gap-2">
                <button onclick="saveNote()" class="flex-1 bg-green-600 text-white py-2 rounded-xl">Запази</button>
                <button onclick="closeNoteModal()" class="flex-1 bg-gray-300 py-2 rounded-xl">Отказ</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentSessionToken = null;
        let selectedProductData = null;
        let currentSessionData = null;
        let originalProductsHtml = null;
        let searchTimeout;

        // ========== HELPER ФУНКЦИИ ==========
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function getProductIcon(name) {
            const icons = {
                'хляб': '🍞', 'мляко': '🥛', 'сирене': '🧀', 'кашкавал': '🧀',
                'месо': '🍖', 'говеждо': '🥩', 'свинско': '🍖', 'пилешко': '🍗',
                'риба': '🐟', 'паста': '🍝', 'ориз': '🍚', 'зеленчук': '🥬',
                'домати': '🍅', 'краставица': '🥒', 'пипер': '🫑', 'плод': '🍎',
                'ябълка': '🍎', 'банан': '🍌', 'портокал': '🍊', 'сок': '🥤',
                'вода': '💧', 'бира': '🍺', 'вино': '🍷', 'кафе': '☕',
                'пица': '🍕', 'салата': '🥗', 'супа': '🥣', 'десерт': '🍰'
            };
            for (let [key, icon] of Object.entries(icons)) {
                if (name.toLowerCase().includes(key)) return icon;
            }
            return '📦';
        }

        // ========== ИНИЦИАЛИЗАЦИЯ ==========
        $(document).ready(function() {
            originalProductsHtml = $('#productsGrid').html();
            refreshSessionsList();
            setInterval(refreshSessionsList, 10000);

            // Търсене на продукти
            $('#searchProducts').on('input', function() {
                clearTimeout(searchTimeout);
                let search = $(this).val().trim();

                if (search.length === 0) {
                    if (originalProductsHtml) {
                        $('#productsGrid').html(originalProductsHtml);
                    } else {
                        location.reload();
                    }
                    return;
                }

                if (search.length < 2) {
                    return;
                }

                $('#productsGrid').html('<div class="col-span-full text-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-green-600"></i><p class="mt-2 text-gray-500">Търсене...</p></div>');

                searchTimeout = setTimeout(() => {
                    $.get(`/shopping-mall/search-products?search=${encodeURIComponent(search)}`, function(products) {
                        if (products.length === 0) {
                            $('#productsGrid').html('<div class="col-span-full text-center py-12"><i class="fas fa-search text-4xl text-gray-400 mb-2"></i><p class="text-gray-500">Няма намерени продукти</p></div>');
                            return;
                        }

                        let html = '<div class="grid grid-cols-2 md:grid-cols-3 gap-4">';
                        products.forEach(p => {
                            const availableQty = p.available_qty || 0;
                            const stockBadge = availableQty <= 0 ? '<div class="text-xs text-red-500 mt-1">✗ Няма наличност</div>' :
                                (availableQty < 5 ? `<div class="text-xs text-orange-500 mt-1">⚠️ Остава: ${availableQty}</div>` :
                                `<div class="text-xs text-green-500 mt-1">✓ Налично: ${availableQty}</div>`);

                            html += `
                                <div class="product-card bg-white border-2 border-gray-100 hover:border-green-500 rounded-2xl p-3 cursor-pointer transition-all hover:shadow-lg text-center"
                                     data-product-id="${p.id}"
                                     data-product-name="${escapeHtml(p.name).replace(/'/g, "\\'")}"
                                     data-product-price="${p.price}"
                                     data-product-unit="${p.unit}"
                                     data-available-qty="${availableQty}"
                                     onclick="addToCurrentSession(this)">
                                    <div class="text-5xl mb-2">${getProductIcon(p.name)}</div>
                                    <div class="font-semibold text-sm leading-tight">${escapeHtml(p.name.substring(0, 30))}</div>
                                    <div class="text-green-600 font-bold mt-2">${parseFloat(p.price).toFixed(2)} €</div>
                                    <div class="text-xs text-gray-400">${p.unit}</div>
                                    ${stockBadge}
                                </div>
                            `;
                        });
                        html += '</div>';
                        $('#productsGrid').html(html);
                    });
                }, 500);
            });
        });

        // ========== ФУНКЦИИ ЗА СЕСИИ ==========
        function selectSession(token) {
            currentSessionToken = token;
            loadSessionDetails(token);
            $('.session-card').removeClass('border-green-500 bg-green-50');
            $(`.session-card[data-session-token="${token}"]`).addClass('border-green-500 bg-green-50');
        }

        function loadSessionDetails(token) {
            $.get(`/shopping-mall/sessions/${token}/summary`, function(data) {
                currentSessionData = data.session;
                const itemsByKiosk = data.items_by_kiosk;
                const currentKioskId = {{ Auth::id() }};

                let customerInfo = '';
                if (data.session.customer_name) {
                    customerInfo += `<div class="text-gray-600 mt-1"><i class="fas fa-user"></i> ${escapeHtml(data.session.customer_name)}</div>`;
                }
                if (data.session.customer_phone) {
                    customerInfo += `<div class="text-gray-500 text-sm"><i class="fas fa-phone"></i> ${escapeHtml(data.session.customer_phone)}</div>`;
                }

                $('#sessionInfo').html(`
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <div>
                                <span class="font-mono font-bold text-xl">${data.session.session_token}</span>
                                ${customerInfo}
                                ${data.session.note ? `<div class="text-xs text-gray-500 mt-1"><i class="fas fa-sticky-note"></i> ${escapeHtml(data.session.note)}</div>` : ''}
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-green-600">${parseFloat(data.session.total_amount).toFixed(2)} €</div>
                                <div class="text-xs text-gray-500">${data.session.items.length} артикула</div>
                            </div>
                        </div>
                    </div>
                `);

                let itemsHtml = '';
                if (data.session.items.length === 0) {
                    itemsHtml = '<div class="text-center text-gray-400 py-8">Няма продукти</div>';
                } else {
                    itemsHtml = '<div class="space-y-2">';
                    data.session.items.forEach((item) => {
                        itemsHtml += `
                            <div class="bg-gray-50 rounded-xl p-3">
                                <div class="flex justify-between">
                                    <div>
                                        <div class="font-medium">${escapeHtml(item.product_name)}</div>
                                        <div class="text-sm text-gray-500">${parseFloat(item.quantity).toFixed(3)} × ${parseFloat(item.unit_price).toFixed(2)} €</div>
                                        <div class="text-xs text-gray-400"><i class="fas fa-store"></i> ${escapeHtml(item.kiosk.name)}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold">${parseFloat(item.total_price).toFixed(2)} €</div>
                                        ${item.kiosk_id == currentKioskId ? `
                                        <div class="flex gap-1 mt-2">
                                            <button onclick="editItemQuantity(${item.id})" class="text-blue-500 text-sm">✏️</button>
                                            <button onclick="removeItem(${item.id})" class="text-red-500 text-sm">🗑️</button>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    itemsHtml += '</div>';
                }

                $('#sessionItems').html(itemsHtml);
                $('#sessionActions').removeClass('hidden');
                $('#sessionTotal').text(parseFloat(data.session.total_amount).toFixed(2) + ' €');
            });
        }

        function refreshSessionsList() {
            $.get('/shopping-mall/sessions', function(sessions) {
                let html = '';
                if (sessions.length === 0) {
                    html = '<div class="text-center text-gray-400 py-12"><i class="fas fa-inbox text-4xl mb-2"></i><p>Няма активни сметки</p><button onclick="openCreateSessionModal()" class="mt-3 text-green-600 hover:underline">Създайте първата сметка</button></div>';
                    $('#sessionsCount').text('0 отворени');
                } else {
                    sessions.forEach(session => {
                        html += `
                            <div class="session-card bg-gray-50 rounded-xl p-3 cursor-pointer hover:shadow-lg" data-session-token="${session.session_token}" onclick="selectSession('${session.session_token}')">
                                <div class="flex justify-between">
                                    <span class="font-mono font-bold">${session.session_token}</span>
                                    <span class="font-bold text-green-600">${parseFloat(session.total_amount).toFixed(2)} €</span>
                                </div>
                                ${session.customer_name ? `<div class="text-sm text-gray-600"><i class="fas fa-user"></i> ${escapeHtml(session.customer_name)}</div>` : ''}
                                <div class="text-xs text-gray-400">${session.created_at}</div>
                            </div>
                        `;
                    });
                    $('#sessionsCount').text(sessions.length + ' отворени');
                }
                $('#sessionsList').html(html);
            });
        }

        // ========== ФУНКЦИИ ЗА ПРОДУКТИ ==========
        function addToCurrentSession(element) {
            if (!currentSessionToken) {
                alert('Моля, първо изберете сметка!');
                return;
            }
            const availableQty = parseFloat($(element).data('available-qty')) || 0;
            if (availableQty <= 0) {
                alert('Този продукт не е наличен в момента!');
                return;
            }
            selectedProductData = {
                id: $(element).data('product-id'),
                name: $(element).data('product-name'),
                price: parseFloat($(element).data('product-price')),
                unit: $(element).data('product-unit'),
                availableQty: availableQty
            };
            $('#modalProductName').text(selectedProductData.name);
            $('#modalAvailableQty').text(selectedProductData.availableQty);
            $('#modalQuantity').val('1');
            $('#quantityModal').removeClass('hidden');
        }

        function setQuantity(qty) { $('#modalQuantity').val(qty); }
        function closeQuantityModal() { $('#quantityModal').addClass('hidden'); selectedProductData = null; }

        function confirmAddToSession() {
            let quantity = parseFloat($('#modalQuantity').val().replace(',', '.'));
            if (isNaN(quantity) || quantity <= 0) {
                alert('Моля, въведете валидно количество');
                return;
            }
            if (quantity > selectedProductData.availableQty) {
                alert(`Няма достатъчна наличност! Максимално: ${selectedProductData.availableQty} ${selectedProductData.unit}`);
                return;
            }
            $.ajax({
                url: '/shopping-mall/items',
                method: 'POST',
                data: {
                    session_token: currentSessionToken,
                    product_id: selectedProductData.id,
                    quantity: quantity,
                    unit_price: selectedProductData.price,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res.success) {
                        loadSessionDetails(currentSessionToken);
                        closeQuantityModal();
                        refreshSessionsList();
                    }
                },
                error: function(xhr) {
                    alert('Грешка: ' + (xhr.responseJSON?.message || 'Неуспешно добавяне'));
                }
            });
        }

        function editItemQuantity(itemId) {
            let newQty = prompt('Въведете ново количество:');
            if (newQty && !isNaN(parseFloat(newQty))) {
                $.ajax({
                    url: `/shopping-mall/items/${itemId}`,
                    method: 'PUT',
                    data: { quantity: parseFloat(newQty), _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) { if (res.success) { loadSessionDetails(currentSessionToken); refreshSessionsList(); } }
                });
            }
        }

        function removeItem(itemId) {
            if (confirm('Сигурни ли сте?')) {
                $.ajax({
                    url: `/shopping-mall/items/${itemId}`,
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) { if (res.success) { loadSessionDetails(currentSessionToken); refreshSessionsList(); } }
                });
            }
        }

        // ========== ФУНКЦИИ ЗА БЕЛЕЖКА ==========
        function openCreateSessionModal() { $('#createSessionModal').removeClass('hidden'); }
        function closeCreateSessionModal() { $('#createSessionModal').addClass('hidden'); }
        function createSession() {
            $.post('/shopping-mall/sessions', {
                customer_name: $('#customerName').val(),
                customer_phone: $('#customerPhone').val(),
                note: $('#sessionNote').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) { if (res.success) location.reload(); });
        }

        function openNoteModal() { $('#noteText').val(currentSessionData?.note || ''); $('#noteModal').removeClass('hidden'); }
        function closeNoteModal() { $('#noteModal').addClass('hidden'); }
        function saveNote() {
            $.ajax({
                url: `/shopping-mall/sessions/${currentSessionData.id}/note`,
                method: 'PUT',
                data: { note: $('#noteText').val(), _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(res) { if (res.success) { loadSessionDetails(currentSessionToken); closeNoteModal(); } }
            });
        }

        function cancelCurrentSession() {
            if (confirm('Анулиране на сметката?')) {
                $.ajax({
                    url: `/shopping-mall/sessions/${currentSessionData.id}`,
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) { if (res.success) location.reload(); }
                });
            }
        }
    </script>
@endpush