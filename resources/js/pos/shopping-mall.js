// resources/js/pos/shopping-mall.js

// Глобални променливи
let currentSessionToken = null;
let selectedProductData = null;
let currentSessionData = null;

// ========== ФУНКЦИИ ЗА СЕСИИ ==========
window.selectSession = function(token) {
    currentSessionToken = token;
    loadSessionDetails(token);

    // Маркираме избраната сесия
    $('.session-card').removeClass('border-primary-500 bg-primary-50');
    $(`.session-card[data-session-token="${token}"]`).addClass('border-primary-500 bg-primary-50');
}

function loadSessionDetails(token) {
    if (!token) return;

    $.get(`/shopping-mall/sessions/${token}/summary`, function(data) {
        if (!data || !data.session) {
            console.error('Invalid session data');
            return;
        }

        currentSessionData = data.session;
        const itemsByKiosk = data.items_by_kiosk || {};

        // Информация за сесията
        let customerInfo = '';
        if (data.session.customer_name) {
            customerInfo = `<div class="text-gray-600 mt-1"><i class="fas fa-user"></i> ${escapeHtml(data.session.customer_name)}</div>`;
        }
        if (data.session.customer_phone) {
            customerInfo += `<div class="text-gray-500 text-sm"><i class="fas fa-phone"></i> ${escapeHtml(data.session.customer_phone)}</div>`;
        }

        $('#sessionInfo').html(`
            <div class="space-y-3">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-xl">${data.session.session_token}</span>
                            <span class="text-xs bg-${data.session.status === 'active' ? 'green' : 'gray'}-100 text-${data.session.status === 'active' ? 'green' : 'gray'}-700 px-2 py-0.5 rounded-full">
                                ${data.session.status === 'active' ? 'Активна' : data.session.status}
                            </span>
                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">
                                <i class="fas fa-store"></i> ${data.total_kiosks} щанда
                            </span>
                        </div>
                        ${customerInfo}
                        ${data.session.note ? `<div class="text-sm text-gray-500 mt-2 p-2 bg-yellow-50 rounded-lg"><i class="fas fa-sticky-note text-yellow-600"></i> ${escapeHtml(data.session.note)}</div>` : ''}
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-primary-600">${parseFloat(data.session.total_amount).toFixed(2)} €</div>
                        <div class="text-xs text-gray-500">${data.session.items.length} артикула</div>
                    </div>
                </div>
                <div class="text-xs text-gray-400">
                    <i class="far fa-calendar-alt"></i> Създадена: ${new Date(data.session.created_at).toLocaleString()}
                </div>
            </div>
        `);

        // Списък с артикулите, групирани по щандове
        let itemsHtml = '<div class="space-y-4">';

        if (data.session.items.length === 0) {
            itemsHtml = '<div class="text-center text-gray-400 py-8">Няма добавени продукти</div>';
        } else {
            for (const [kioskName, kioskData] of Object.entries(itemsByKiosk)) {
                const canEditKiosk = window.canEditAll || (kioskData.kiosk_id == window.currentUserId);

                itemsHtml += `
                    <div class="border-2 border-gray-200 rounded-xl overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-3 border-b flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-store text-purple-600"></i>
                                <span class="font-semibold text-purple-800">${escapeHtml(kioskName)}</span>
                                <span class="text-xs bg-purple-200 text-purple-700 px-2 py-0.5 rounded-full">
                                    ${kioskData.items.length} арт.
                                </span>
                            </div>
                            <div class="font-bold text-purple-700">
                                ${parseFloat(kioskData.subtotal).toFixed(2)} €
                            </div>
                        </div>
                        <div class="p-3 space-y-2 bg-white">
                `;

                kioskData.items.forEach((item) => {
                    itemsHtml += `
                        <div class="flex justify-between items-start hover:bg-gray-50 p-2 rounded-lg transition">
                            <div class="flex-1">
                                <div class="font-medium">${escapeHtml(item.product_name)}</div>
                                <div class="text-sm text-gray-500">
                                    ${parseFloat(item.quantity).toFixed(item.quantity % 1 === 0 ? 0 : 3)} × ${parseFloat(item.unit_price).toFixed(2)} €
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    <i class="far fa-clock"></i> ${new Date(item.created_at).toLocaleTimeString()}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold">${parseFloat(item.total_price).toFixed(2)} €</div>
                                ${canEditKiosk ? `
                                    <div class="flex gap-1 mt-2 justify-end">
                                        <button onclick="editItemQuantity(${item.id})" class="text-blue-500 hover:text-blue-700 text-sm px-2 py-1 rounded">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="removeItem(${item.id})" class="text-red-500 hover:text-red-700 text-sm px-2 py-1 rounded">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                });

                itemsHtml += `
                        </div>
                    </div>
                `;
            }
        }
        itemsHtml += '</div>';

        $('#sessionItems').html(itemsHtml);
        $('#sessionActions').removeClass('hidden');
        $('#sessionTotal').text(parseFloat(data.session.total_amount).toFixed(2) + ' €');
    }).fail(function(error) {
        console.error('Error loading session:', error);
        alert('Грешка при зареждане на детайлите на сметката');
    });
}

window.openCreateSessionModal = function() {
    $('#customerName').val('');
    $('#customerPhone').val('');
    $('#sessionNote').val('');
    $('#createSessionModal').removeClass('hidden');
}

window.closeCreateSessionModal = function() {
    $('#createSessionModal').addClass('hidden');
}

window.createSession = function() {
    $.post('/shopping-mall/sessions', {
        customer_name: $('#customerName').val(),
        customer_phone: $('#customerPhone').val(),
        note: $('#sessionNote').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    }, function(res) {
        if (res.success) {
            location.reload();
        }
    }).fail(function(xhr) {
        alert('Грешка при създаване на сметка: ' + (xhr.responseJSON?.message || 'Неизвестна грешка'));
    });
}

// ========== ФУНКЦИИ ЗА ПРОДУКТИ ==========
window.addToCurrentSession = function(element) {
    if (!currentSessionToken) {
        alert('Моля, първо изберете сметка от списъка!');
        return;
    }

    selectedProductData = {
        id: $(element).data('product-id'),
        name: $(element).data('product-name'),
        price: parseFloat($(element).data('product-price')),
        unit: $(element).data('product-unit')
    };

    $('#modalProductName').text(selectedProductData.name);
    $('#modalQuantity').val('1');
    $('#quantityModal').removeClass('hidden');
}

window.setQuantity = function(qty) {
    $('#modalQuantity').val(qty);
}

window.closeQuantityModal = function() {
    $('#quantityModal').addClass('hidden');
    selectedProductData = null;
}

window.confirmAddToSession = function() {
    let quantity = parseFloat($('#modalQuantity').val().replace(',', '.'));

    if (isNaN(quantity) || quantity <= 0) {
        alert('Моля, въведете валидно количество');
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

window.editItemQuantity = function(itemId) {
    let newQty = prompt('Въведете ново количество:');
    if (newQty && !isNaN(parseFloat(newQty))) {
        $.ajax({
            url: `/shopping-mall/items/${itemId}`,
            method: 'PUT',
            data: {
                quantity: parseFloat(newQty),
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res.success) {
                    loadSessionDetails(currentSessionToken);
                    refreshSessionsList();
                }
            }
        });
    }
}

window.removeItem = function(itemId) {
    if (confirm('Сигурни ли сте, че искате да премахнете този продукт?')) {
        $.ajax({
            url: `/shopping-mall/items/${itemId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res.success) {
                    loadSessionDetails(currentSessionToken);
                    refreshSessionsList();
                }
            }
        });
    }
}

// ========== ФУНКЦИИ ЗА БЕЛЕЖКА ==========
window.openNoteModal = function() {
    $('#noteText').val(currentSessionData?.note || '');
    $('#noteModal').removeClass('hidden');
}

window.closeNoteModal = function() {
    $('#noteModal').addClass('hidden');
}

window.saveNote = function() {
    if (!currentSessionData) return;

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
                refreshSessionsList();
            }
        }
    });
}

window.cancelCurrentSession = function() {
    if (confirm('Сигурни ли сте, че искате да анулирате цялата сметка? Това действие е необратимо!')) {
        if (!currentSessionData) return;

        $.ajax({
            url: `/shopping-mall/sessions/${currentSessionData.id}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res.success) {
                    currentSessionToken = null;
                    location.reload();
                }
            }
        });
    }
}

// ========== ФУНКЦИИ ЗА КАСИЕР ==========
window.processPayment = function() {
    if (!currentSessionData) return;

    $('#paymentSessionToken').text(currentSessionData.session_token);
    $('#paymentTotal').text(parseFloat(currentSessionData.total_amount).toFixed(2) + ' €');
    $('#paymentAmount').val(parseFloat(currentSessionData.total_amount).toFixed(2));
    $('#paymentModal').removeClass('hidden');
    $('#changeInfo').addClass('hidden');

    $('#paymentAmount').off('input').on('input', function() {
        let paid = parseFloat($(this).val().replace(',', '.')) || 0;
        let total = parseFloat(currentSessionData.total_amount);
        let change = paid - total;

        if (change >= 0 && paid > 0) {
            $('#changeAmount').text(change.toFixed(2));
            $('#changeInfo').removeClass('hidden');
        } else {
            $('#changeInfo').addClass('hidden');
        }
    });
}

window.closePaymentModal = function() {
    $('#paymentModal').addClass('hidden');
}

window.confirmPayment = function() {
    let paid = parseFloat($('#paymentAmount').val().replace(',', '.')) || 0;
    let method = $('#paymentMethod').val();

    if (paid < currentSessionData.total_amount) {
        alert('Въведената сума е по-малка от дължимата!');
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
        success: function(res) {
            if (res.success) {
                alert(`✅ Плащането е успешно!\nСметка: ${res.session.session_token}\nБележка №: ${res.receipt_number}`);
                location.reload();
            }
        },
        error: function(xhr) {
            alert('Грешка: ' + (xhr.responseJSON?.message || 'Неуспешно плащане'));
        }
    });
}

// ========== HELPER ФУНКЦИИ ==========
function refreshSessionsList() {
    $.get('/shopping-mall/sessions', function(sessions) {
        let html = '';
        if (sessions.length === 0) {
            html = `
                <div class="text-center text-gray-400 py-12">
                    <i class="fas fa-inbox text-5xl mb-3 opacity-50"></i>
                    <p class="text-lg">Няма активни сметки</p>
                    <button onclick="openCreateSessionModal()" class="mt-3 text-primary-600 hover:underline">
                        <i class="fas fa-plus"></i> Създайте първата сметка
                    </button>
                </div>
            `;
            $('#sessionsCount').text('0 отворени');
        } else {
            sessions.forEach(session => {
                const itemsCount = session.items_count || 0;
                html += `
                    <div class="session-card bg-gray-50 rounded-xl p-4 cursor-pointer hover:shadow-lg transition-all border-2 border-transparent hover:border-primary-300"
                         data-session-token="${session.session_token}"
                         onclick="selectSession('${session.session_token}')">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono font-bold text-lg">${session.session_token}</span>
                                    <span class="text-xs bg-primary-100 text-primary-700 px-2 py-0.5 rounded-full">
                                        ${itemsCount} арт.
                                    </span>
                                </div>
                                ${session.customer_name ? `<div class="text-sm text-gray-600 mt-1"><i class="fas fa-user"></i> ${escapeHtml(session.customer_name)}</div>` : ''}
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-primary-600">${parseFloat(session.total_amount).toFixed(2)} €</div>
                            </div>
                        </div>
                        ${session.note ? `<div class="text-xs text-gray-500 mt-2 bg-white p-2 rounded-lg"><i class="fas fa-sticky-note"></i> ${escapeHtml(session.note.substring(0, 50))}</div>` : ''}
                        <div class="flex justify-between items-center mt-2">
                            <div class="text-xs text-gray-400">
                                <i class="far fa-clock"></i> ${session.created_at}
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="fas fa-store text-gray-400 text-xs"></i>
                                <span class="text-xs text-gray-500">Мулти-щанд</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#sessionsCount').text(sessions.length + ' отворени');
        }
        $('#sessionsList').html(html);
    });
}

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
        'вода': '💧', 'бира': '🍺', 'вино': '🍷', 'кафе': '☕'
    };
    for (let [key, icon] of Object.entries(icons)) {
        if (name.toLowerCase().includes(key)) return icon;
    }
    return '📦';
}

// Търсене на продукти
let searchTimeout;
$(document).ready(function() {
    // Инициализация
    refreshSessionsList();
    
    // Автоматично обновяване на списъка на всеки 10 секунди
    setInterval(refreshSessionsList, 10000);
    
    // Търсене
    $('#searchProducts').on('input', function() {
        clearTimeout(searchTimeout);
        let search = $(this).val();

        if (search.length < 2) {
            location.reload();
            return;
        }

        searchTimeout = setTimeout(() => {
            $.get(`/shopping-mall/search-products?search=${encodeURIComponent(search)}`, function(products) {
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
        }, 300);
    });
});