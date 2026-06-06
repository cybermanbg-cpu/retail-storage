@extends('layouts.app')

@section('title', 'Shopping Mall POS - Мултищандова продажба')

@section('hide_navigation', true)

@section('content')
    <div class="container-fluid px-4 py-3 h-screen flex flex-col">

        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-2xl shadow-lg p-4 mb-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-cash-register text-2xl"></i>
                        <h1 class="text-xl font-bold">Каса: {{ Auth::user()->name }}</h1>
                        <span class="text-xs bg-white/20 px-2 py-1 rounded-full">
                            <i class="fas fa-money-bill"></i> Финализиране
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-purple-200">
                        <span><i class="fas fa-building"></i> {{ $owner?->name ?? '-' }}</span>
                        <span><i class="fas fa-warehouse"></i> {{ $storageObject?->name ?? '-' }}</span>
                        <span><i class="fas fa-clock"></i> {{ now()->format('H:i') }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('shopping-mall.kiosk') }}"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition shadow-md">
                        <i class="fas fa-exchange-alt mr-1"></i> Към Щанд
                    </a>
                    <a href="{{ route('home') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition shadow-md">
                        <i class="fas fa-home mr-1"></i> Начало
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

            <!-- Активни сесии -->
            <div class="col-span-4 bg-white rounded-2xl shadow flex flex-col min-h-0">
                <div class="p-4 border-b flex-shrink-0">
                    <h2 class="text-xl font-bold">Активни сметки</h2>
                    <p class="text-sm text-gray-500" id="sessionsCount">{{ $activeSessions->count() }} отворени</p>
                </div>
                <div id="sessionsList" class="flex-1 overflow-y-auto p-4 space-y-3 min-h-0">
                    @forelse($activeSessions as $session)
                        <div class="session-card bg-gray-50 rounded-xl p-4 cursor-pointer hover:shadow-lg transition-all border-2 border-transparent hover:border-primary-300"
                            data-session-token="{{ $session->session_token }}"
                            onclick="selectSession('{{ $session->session_token }}')">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-lg">{{ $session->session_token }}</span>
                                        @php
                                            $itemsCount = $session->items->count();
                                        @endphp
                                        @if ($itemsCount > 0)
                                            <span class="text-xs bg-primary-100 text-primary-700 px-2 py-0.5 rounded-full">
                                                {{ $itemsCount }} арт.
                                            </span>
                                        @endif
                                    </div>
                                    @if ($session->customer_name)
                                        <div class="text-sm text-gray-600 mt-1">
                                            <i class="fas fa-user"></i> {{ $session->customer_name }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-primary-600 text-lg">
                                        {{ number_format($session->total_amount, 2) }} €</div>
                                </div>
                            </div>
                            @if ($session->note)
                                <div class="text-xs text-gray-500 mt-2 bg-white p-2 rounded-lg">
                                    <i class="fas fa-sticky-note text-gray-400"></i> {{ Str::limit($session->note, 60) }}
                                </div>
                            @endif
                            <div class="text-xs text-gray-400 mt-2">
                                <i class="far fa-clock"></i> {{ $session->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-400 py-12">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Няма активни сметки</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Продукти -->
            <div class="col-span-5 bg-white rounded-2xl shadow flex flex-col min-h-0">
                <div class="p-4 border-b flex-shrink-0">
                    <input type="text" id="searchProducts" class="w-full px-4 py-3 border-2 rounded-xl"
                        placeholder="Търси продукт...">
                </div>
                <div id="productsGrid" class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($products as $product)
                            @php
                                $variant = $product->variants->first();
                                $stock = null;
                                $availableQty = 0;
                                if ($variant && isset($storageObject)) {
                                    $stock = \App\Models\Stock::where('product_variant_id', $variant->id)
                                        ->where('storage_object_id', $storageObject->id)
                                        ->first();
                                    $availableQty = $stock ? $stock->available : 0;
                                }
                            @endphp
                            <div class="product-card bg-white border-2 border-gray-100 hover:border-primary-500 rounded-2xl p-3 cursor-pointer transition-all hover:shadow-lg text-center"
                                data-product-id="{{ $product->id }}" data-product-name="{{ addslashes($product->name) }}"
                                data-product-price="{{ $product->base_price }}" data-available-qty="{{ $availableQty }}"
                                onclick="addToCurrentSession(this)">
                                <div class="text-5xl mb-2">{{ getProductIcon($product->name) }}</div>
                                <div class="font-semibold text-sm leading-tight">{{ Str::limit($product->name, 30) }}</div>
                                <div class="text-primary-600 font-bold mt-2">{{ number_format($product->base_price, 2) }}  <span class="text-black text-xs">€/{{ $product->unit_symbol ?? 'бр.' }}</span>
                                </div>
                                @if ($availableQty <= 0)
                                    <div class="text-xs text-red-500 mt-1">✗ Няма наличност</div>
                                @elseif($availableQty < 5)
                                    <div class="text-xs text-orange-500 mt-1">⚠️ Остава: {{ $availableQty }}</div>
                                @else
                                    <div class="text-xs text-green-500 mt-1">✓ Налично: {{ $availableQty }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Количка -->
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
                        <span id="sessionTotal" class="text-2xl font-bold text-primary-600">0.00 €</span>
                    </div>
                    @if ($isCashier)
                        <button onclick="processPayment()"
                            class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-semibold">
                            <i class="fas fa-cash-register mr-2"></i> Плащане
                        </button>
                    @else
                        <div class="grid grid-cols-2 gap-2">
                            <button onclick="openNoteModal()"
                                class="bg-gray-300 hover:bg-gray-400 py-2 rounded-xl">Бележка</button>
                            <button onclick="cancelCurrentSession()"
                                class="bg-red-500 hover:bg-red-600 text-white py-2 rounded-xl">Анулирай</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Модали -->
    <div id="createSessionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Нова сметка</h3>
            <input type="text" id="customerName" class="w-full border rounded-xl p-2 mb-3"
                placeholder="Име на клиент">
            <input type="text" id="customerPhone" class="w-full border rounded-xl p-2 mb-3" placeholder="Телефон">
            <textarea id="sessionNote" class="w-full border rounded-xl p-2 mb-3" rows="2" placeholder="Бележка"></textarea>
            <div class="flex gap-2">
                <button onclick="createSession()" class="flex-1 bg-primary-600 text-white py-2 rounded-xl">Създай</button>
                <button onclick="closeCreateSessionModal()" class="flex-1 bg-gray-300 py-2 rounded-xl">Отказ</button>
            </div>
        </div>
    </div>

    <div id="quantityModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Количество</h3>
            <p id="modalProductName" class="mb-3 font-semibold"></p>
            <div class="mb-4">
                <p class="text-gray-600 mb-1">Продукт:</p>
                <p id="modalProductName" class="font-semibold text-lg">-</p>
            </div>
            <div class="mb-2">
                <p class="text-sm text-gray-500">Наличност: <span id="modalAvailableQty"
                        class="font-semibold text-green-600">0</span></p>
            </div>
            <input type="text" id="modalQuantity" value="1"
                class="w-full text-center text-2xl border rounded-xl p-3 mb-3">
            <div class="grid grid-cols-4 gap-2 mb-4">
                <button onclick="setQuantity(0.5)" class="bg-gray-100 py-2 rounded">0.5</button>
                <button onclick="setQuantity(1)" class="bg-gray-100 py-2 rounded">1</button>
                <button onclick="setQuantity(2)" class="bg-gray-100 py-2 rounded">2</button>
                <button onclick="setQuantity(3)" class="bg-gray-100 py-2 rounded">3</button>
            </div>
            <div class="flex gap-2">
                <button onclick="confirmAddToSession()"
                    class="flex-1 bg-green-600 text-white py-2 rounded-xl">Добави</button>
                <button onclick="closeQuantityModal()" class="flex-1 bg-gray-300 py-2 rounded-xl">Отказ</button>
            </div>
        </div>
    </div>

    <div id="noteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Бележка</h3>
            <textarea id="noteText" class="w-full border rounded-xl p-2 mb-3" rows="3"></textarea>
            <div class="flex gap-2">
                <button onclick="saveNote()" class="flex-1 bg-primary-600 text-white py-2 rounded-xl">Запази</button>
                <button onclick="closeNoteModal()" class="flex-1 bg-gray-300 py-2 rounded-xl">Отказ</button>
            </div>
        </div>
    </div>
    {{-- модал за плащане --}}
    {{-- модал за плащане --}}
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 w-96">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Плащане</h3>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <p class="mb-1 text-sm text-gray-600">Сметка:</p>
            <p class="mb-3 font-mono font-bold" id="paymentSessionToken"></p>

            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600">Обща сума:</p>
                <p class="text-2xl font-bold text-primary-600" id="paymentTotal">0.00 €</p>
            </div>

            <label class="block text-sm font-medium mb-1">Въведена сума:</label>
            <input type="text" id="paymentAmount" class="w-full border rounded-xl p-3 mb-3 text-xl text-center"
                placeholder="0.00">

            <!-- ⭐ БЪРЗИ СУМИ - 5 колони в ред за тач екран ⭐ -->
            <div class="grid grid-cols-5 gap-2 mb-3">
                <button type="button" onclick="setPaymentAmount(5)"
                    class="bg-gray-100 hover:bg-gray-200 active:bg-gray-300 py-3 rounded-xl text-base font-medium transition touch-manipulation">5€</button>
                <button type="button" onclick="setPaymentAmount(10)"
                    class="bg-gray-100 hover:bg-gray-200 active:bg-gray-300 py-3 rounded-xl text-base font-medium transition touch-manipulation">10€</button>
                <button type="button" onclick="setPaymentAmount(20)"
                    class="bg-gray-100 hover:bg-gray-200 active:bg-gray-300 py-3 rounded-xl text-base font-medium transition touch-manipulation">20€</button>
                <button type="button" onclick="setPaymentAmount(50)"
                    class="bg-gray-100 hover:bg-gray-200 active:bg-gray-300 py-3 rounded-xl text-base font-medium transition touch-manipulation">50€</button>
                <button type="button" onclick="setPaymentAmount(100)"
                    class="bg-gray-100 hover:bg-gray-200 active:bg-gray-300 py-3 rounded-xl text-base font-medium transition touch-manipulation">100€</button>
            </div>

            <label class="block text-sm font-medium mb-1">Начин на плащане:</label>
            <select id="paymentMethod" class="w-full border rounded-xl p-3 mb-3 text-base">
                <option value="cash">💵 В брой</option>
                <option value="card">💳 Карта</option>
            </select>

            <div id="changeInfo" class="hidden mb-3 p-3 bg-green-100 rounded-lg">
                <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                <span>Ресто: </span><span id="changeAmount" class="font-bold text-green-700">0.00</span> €
            </div>

            <div class="flex gap-2">
                <button id="confirmPaymentBtn" onclick="confirmPayment()"
                    class="flex-1 bg-green-600 hover:bg-green-700 active:bg-green-800 text-white py-3 rounded-xl font-semibold transition touch-manipulation">
                    <i class="fas fa-check-circle mr-2"></i> Потвърди
                </button>
                <button onclick="closePaymentModal()"
                    class="flex-1 bg-gray-300 hover:bg-gray-400 active:bg-gray-500 text-gray-800 py-3 rounded-xl font-semibold transition touch-manipulation">
                    <i class="fas fa-times mr-2"></i> Отказ
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentSessionToken = null;
        let selectedProductData = null;
        let currentSessionData = null;

        // ========== HELPER ФУНКЦИИ ==========
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function getProductIcon(name) {
            const icons = {
                'хляб': '🍞',
                'мляко': '🥛',
                'сирене': '🧀',
                'кашкавал': '🧀',
                'месо': '🍖',
                'говеждо': '🥩',
                'свинско': '🍖',
                'пилешко': '🍗',
                'риба': '🐟',
                'паста': '🍝',
                'ориз': '🍚',
                'зеленчук': '🥬',
                'домати': '🍅',
                'краставица': '🥒',
                'пипер': '🫑',
                'плод': '🍎',
                'ябълка': '🍎',
                'банан': '🍌',
                'портокал': '🍊',
                'сок': '🥤',
                'вода': '💧',
                'бира': '🍺',
                'вино': '🍷',
                'кафе': '☕',
                'пица': '🍕',
                'салата': '🥗',
                'супа': '🥣',
                'десерт': '🍰'
            };
            for (let [key, icon] of Object.entries(icons)) {
                if (name.toLowerCase().includes(key)) return icon;
            }
            return '📦';
        }

        // ========== СЕСИИ ==========
        function selectSession(token) {
            currentSessionToken = token;
            loadSessionDetails(token);
            $('.session-card').removeClass('border-primary-500 bg-primary-50');
            $(`.session-card[data-session-token="${token}"]`).addClass('border-primary-500 bg-primary-50');
        }

        function loadSessionDetails(token) {
            $.get(`/shopping-mall/sessions/${token}/summary`, function(data) {
                currentSessionData = data.session;

                let customerInfo = '';
                if (data.session.customer_name) {
                    customerInfo =
                        `<div class="text-gray-600"><i class="fas fa-user"></i> ${escapeHtml(data.session.customer_name)}</div>`;
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
                        <div class="text-2xl font-bold text-primary-600">${parseFloat(data.session.total_amount).toFixed(2)} €</div>
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
                sessions.forEach(session => {
                    html += `
                <div class="session-card bg-gray-50 rounded-xl p-3 cursor-pointer hover:shadow-lg" data-session-token="${session.session_token}" onclick="selectSession('${session.session_token}')">
                    <div class="flex justify-between">
                        <span class="font-mono font-bold">${session.session_token}</span>
                        <span class="font-bold text-primary-600">${parseFloat(session.total_amount).toFixed(2)} €</span>
                    </div>
                    ${session.customer_name ? `<div class="text-sm text-gray-600"><i class="fas fa-user"></i> ${escapeHtml(session.customer_name)}</div>` : ''}
                    <div class="text-xs text-gray-400">${session.created_at}</div>
                </div>
            `;
                });
                $('#sessionsList').html(html ||
                    '<div class="text-center text-gray-400 py-8">Няма активни сметки</div>');
                $('#sessionsCount').text(sessions.length + ' отворени');
            });
        }

        // Търсене на продукти за касата
        let searchTimeout;
        let originalProductsHtml = null;

        $(document).ready(function() {
            originalProductsHtml = $('#productsGrid').html();
            refreshSessionsList();
            setInterval(refreshSessionsList, 10000);
        });

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

            $('#productsGrid').html(
                '<div class="col-span-full text-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-primary-600"></i><p class="mt-2 text-gray-500">Търсене...</p></div>'
            );

            searchTimeout = setTimeout(() => {
                $.get(`/shopping-mall/search-products?search=${encodeURIComponent(search)}`, function(
                    products) {
                    if (products.length === 0) {
                        $('#productsGrid').html(
                            '<div class="col-span-full text-center py-12"><i class="fas fa-search text-4xl text-gray-400 mb-2"></i><p class="text-gray-500">Няма намерени продукти</p></div>'
                        );
                        return;
                    }

                    let html = '<div class="grid grid-cols-2 md:grid-cols-3 gap-4">';
                    products.forEach(p => {
                        html += `
                            <div class="product-card bg-white border-2 border-gray-100 hover:border-primary-500 rounded-2xl p-3 cursor-pointer transition-all hover:shadow-lg text-center"
                                 data-product-id="${p.id}"
                                 data-product-name="${escapeHtml(p.name).replace(/'/g, "\\'")}"
                                 data-product-price="${p.price}"
                                 data-product-unit="${p.unit}"
                                 onclick="addToCurrentSession(this)">
                                <div class="text-5xl mb-2">${getProductIcon(p.name)}</div>
                                <div class="font-semibold text-sm leading-tight">${escapeHtml(p.name.substring(0, 30))}</div>
                                <div class="text-primary-600 font-bold mt-2">${parseFloat(p.price).toFixed(2)} €</div>
                                <div class="text-xs text-gray-400">${p.unit}</div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    $('#productsGrid').html(html);
                });
            }, 500);
        });

        // ========== ПРОДУКТИ ==========
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
                availableQty: availableQty
            };

            $('#modalProductName').text(selectedProductData.name);
            $('#modalAvailableQty').text(selectedProductData.availableQty);
            $('#modalQuantity').val('1');
            $('#quantityModal').removeClass('hidden');
        }

        function setQuantity(qty) {
            $('#modalQuantity').val(qty);
        }

        function closeQuantityModal() {
            $('#quantityModal').addClass('hidden');
        }

        // ========== ФУНКЦИИ ЗА БЪРЗИ СУМИ ==========
        function setPaymentAmount(amount) {
            // Гарантираме, че amount е число
            let numericAmount = parseFloat(amount);
            if (isNaN(numericAmount)) {
                numericAmount = 0;
            }

            let total = parseFloat(currentSessionData.total_amount);
            if (isNaN(total)) {
                total = 0;
            }

            let finalAmount = numericAmount >= total ? numericAmount : total;
            $('#paymentAmount').val(finalAmount.toFixed(2));
            $('#paymentAmount').trigger('input'); // Тригва изчисляването на рестото
            $('#paymentAmount').focus();
            $('#paymentAmount').select();
        }

        function confirmAddToSession() {
            let quantity = parseFloat($('#modalQuantity').val().replace(',', '.'));

            if (isNaN(quantity) || quantity <= 0) {
                alert('Моля, въведете валидно количество');
                return;
            }

            // Проверка за наличност
            if (quantity > selectedProductData.availableQty) {
                alert(`Няма достатъчна наличност! Максимално: ${selectedProductData.availableQty}`);
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
                    let errorMsg = 'Неуспешно добавяне';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert('Грешка: ' + errorMsg);
                }
            });
        }


        // ========== СЪЗДАВАНЕ ==========
        function openCreateSessionModal() {
            $('#createSessionModal').removeClass('hidden');
        }

        function closeCreateSessionModal() {
            $('#createSessionModal').addClass('hidden');
        }

        function createSession() {
            $.post('/shopping-mall/sessions', {
                customer_name: $('#customerName').val(),
                customer_phone: $('#customerPhone').val(),
                note: $('#sessionNote').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {
                if (res.success) location.reload();
            });
        }

        // ========== БЕЛЕЖКА ==========
        function openNoteModal() {
            $('#noteText').val(currentSessionData?.note || '');
            $('#noteModal').removeClass('hidden');
        }

        function closeNoteModal() {
            $('#noteModal').addClass('hidden');
        }

        function saveNote() {
            $.ajax({
                url: `/shopping-mall/sessions/${currentSessionData.id}/note`,
                method: 'PUT',
                data: {
                    note: $('#noteText').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res.success) {
                        loadSessionDetails(currentSessionToken);
                        closeNoteModal();
                    }
                }
            });
        }

        function cancelCurrentSession() {
            if (confirm('Анулиране на сметката?')) {
                $.ajax({
                    url: `/shopping-mall/sessions/${currentSessionData.id}`,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.success) location.reload();
                    }
                });
            }
        }

        // ========== ПЛАЩАНЕ ==========
        function processPayment() {
            if (!currentSessionData) return;
            $('#paymentSessionToken').text(currentSessionData.session_token);
            $('#paymentTotal').text(parseFloat(currentSessionData.total_amount).toFixed(2) + ' €');
            $('#paymentAmount').val(parseFloat(currentSessionData.total_amount).toFixed(2));
            $('#paymentModal').removeClass('hidden');

            // Фокус върху полето за сума
            setTimeout(() => {
                $('#paymentAmount').focus();
                $('#paymentAmount').select();
            }, 100);

            // Автоматично преизчисляване на рестото
            $('#paymentAmount').off('input').on('input', function() {
                let paid = parseFloat($(this).val().replace(',', '.')) || 0;
                let change = paid - currentSessionData.total_amount;
                if (change >= 0) {
                    $('#changeAmount').text(change.toFixed(2));
                    $('#changeInfo').removeClass('hidden');
                } else {
                    $('#changeInfo').addClass('hidden');
                }
            });
        }

        // Enter да потвърждава плащането
        $(document).off('keypress', '#paymentAmount').on('keypress', '#paymentAmount', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmPayment();
            }
        });

        // Автоматично маркиране на текста при фокус
        $(document).off('focus', '#paymentAmount').on('focus', '#paymentAmount', function() {
            $(this).select();
        });

        function closePaymentModal() {
            $('#paymentModal').addClass('hidden');
            $('#changeInfo').addClass('hidden');
        }

        function confirmPayment() {
            let paid = parseFloat($('#paymentAmount').val().replace(',', '.')) || 0;
            let method = $('#paymentMethod').val();

            if (paid < currentSessionData.total_amount) {
                alert('Въведената сума е по-малка от дължимата!');
                $('#paymentAmount').focus();
                $('#paymentAmount').select();
                return;
            }

            $.ajax({
                url: '/shopping-mall/payment',
                method: 'POST',
                data: {
                    session_token: currentSessionToken,
                    payment_method: method,
                    amount_paid: paid,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    // Деактивиране на бутона за потвърждение
                    const confirmBtn = $('#paymentModal button:contains("Потвърди")');
                    confirmBtn.prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin mr-2"></i>Обработване...');
                },
                success: function(res) {
                    if (res.success) {
                        alert(
                            `✅ Плащането е успешно!\nСметка: ${res.session.session_token}\nСума: ${parseFloat(res.session.total_amount).toFixed(2)} €`
                        );
                        location.reload();
                    } else {
                        alert('❌ Грешка: ' + res.message);
                        // Възстановяване на бутона
                        $('#paymentModal button:contains("Потвърди")').prop('disabled', false).html(
                            '<i class="fas fa-check-circle mr-2"></i> Потвърди');
                    }
                },
                error: function(xhr) {
                    alert('❌ Грешка при плащане!');
                    $('#paymentModal button:contains("Потвърди")').prop('disabled', false).html(
                        '<i class="fas fa-check-circle mr-2"></i> Потвърди');
                }
            });
        }

        // ========== HELPER ==========
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Стартиране
        $(document).ready(function() {
            refreshSessionsList();
            setInterval(refreshSessionsList, 10000);
        });
    </script>
@endpush
