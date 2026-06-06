// Restaurant POS JavaScript
let currentCategoryId = null;
let currentItems = [];
let storageObjectId = null;
let tableNumber = localStorage.getItem('tableNumber') || 12;
let selectedProduct = null;
let currentCartId = null;

// Инициализация
function initRestaurantPOS(storageId, cartId, items) {
    storageObjectId = storageId;
    currentCartId = cartId;

    // Валидация на critical IDs
    if (!storageObjectId) {
        console.error('storageObjectId is missing!');
    }
    if (!currentCartId) {
        console.warn('currentCartId is missing, creating new cart might be needed');
    }

    // Защита от невалидни данни
    try {
        currentItems = Array.isArray(items) ? items : (items ? JSON.parse(items) : []);
    } catch (e) {
        console.error('Error parsing items:', e);
        currentItems = [];
    }

    if (!Array.isArray(currentItems)) {
        currentItems = [];
    }

    $('#tableNumber').text(tableNumber);
    updateCart();
    attachGlobalEvents();

    if ($('.category-btn').first().length) {
        $('.category-btn').first().click();
    } else {
        showAllProducts();
    }
}


function saveCart() {
    if (!currentCartId) return;

    // Подготвяме данните за изпращане
    const itemsToSave = currentItems.map(item => ({
        product_id: item.product_id,
        quantity: parseFloat(item.quantity) || 0,
        price: parseFloat(item.price) || 0,
        original_price: parseFloat(item.original_price) || 0,
        discount: parseFloat(item.discount) || 0,
        product_name: item.product_name
    }));

    $.ajax({
        url: `/pos/cart/${currentCartId}`,
        method: 'PUT',
        data: {
            items: itemsToSave,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        error: function (xhr) {
            console.error('Error saving cart:', xhr);
        }
    });
}

function clearCart() {
    currentItems = [];
    updateCart();
    saveCart();
}

function attachGlobalEvents() {
    // Category buttons
    $(document).on('click', '.category-btn', function () {
        currentCategoryId = $(this).data('category-id');
        $('.category-btn').removeClass('bg-primary-600 text-white shadow-md').addClass('bg-gray-100');
        $(this).addClass('bg-primary-600 text-white shadow-md');
        loadProducts(currentCategoryId);
        $('#searchInput').val('');
    });

    $('#clearCartBtn').off('click').on('click', function () {
        if (currentItems.length && confirm('Изчисти цялата поръчка?')) {
            currentItems = [];
            updateCart();
        }
    });

    $('#checkoutBtn').off('click').on('click', openPaymentModal);

    $('#clearSearchBtn, #clearSearchBtnAlt').off('click').on('click', function () {
        $('#searchInput').val('');
        if (currentCategoryId) {
            loadProducts(currentCategoryId);
        } else {
            showAllProducts();
        }
    });

    // Събития за модала - без ограничения на клавишите
    $(document).ready(function () {
        // Бързи избори
        $(document).on('click', '.qty-preset', function () {
            let value = $(this).data('value');
            $('#modalQuantity').val(value);
            updateModalTotal();
            $('#modalQuantity').focus();
        });

        // Свободно въвеждане - без ограничения
        $('#modalQuantity').off('input').on('input', function () {
            updateModalTotal();
        });

        // Enter за потвърждение
        $('#modalQuantity').off('keypress').on('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmQuantity();
            }
        });

        $('#confirmQuantityBtn').off('click').on('click', confirmQuantity);
        $('#cancelQuantityBtn').off('click').on('click', closeQuantityModal);

        // Затваряне с ESC
        $(document).off('keydown').on('keydown', function (e) {
            if (e.key === 'Escape' && !$('#quantityModal').hasClass('hidden')) {
                closeQuantityModal();
            }
        });
    });

    // Модал за количество - събития
    $('#qtyMinus').off('click').on('click', function () {
        let qty = parseQuantityInput($('#modalQuantity').val());
        if (qty > 0.001) $('#modalQuantity').val((qty - 0.001).toFixed(3));
        updateModalTotal();
    });

    $('#qtyPlus').off('click').on('click', function () {
        let qty = parseQuantityInput($('#modalQuantity').val());
        $('#modalQuantity').val((qty + 0.001).toFixed(3));
        updateModalTotal();
    });

    $('#modalQuantity').off('input').on('input', function () {
        let qty = parseQuantityInput($(this).val());
        if (qty < 0) qty = 0;
        $(this).val(qty.toFixed(3));
        updateModalTotal();
    });

    $('#confirmQuantityBtn').off('click').on('click', confirmQuantity);
    $('#cancelQuantityBtn').off('click').on('click', closeQuantityModal);

    $(document).off('keydown').on('keydown', function (e) {
        if (e.key === 'Escape' && !$('#quantityModal').hasClass('hidden')) closeQuantityModal();
    });

    // Търсене на продукти за Restaurant POS
    let searchTimeout;
    $('#searchInput').off('input').on('input', function () {
        clearTimeout(searchTimeout);
        let search = $(this).val().trim();

        if (search.length === 0) {
            if (currentCategoryId) {
                loadProducts(currentCategoryId);
            } else {
                showAllProducts();
            }
            return;
        }

        if (search.length < 3) return;

        $('#productsGrid').html('<div class="col-span-full text-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-primary-600"></i><p class="mt-2 text-gray-500">Търсене...</p></div>');

        searchTimeout = setTimeout(() => {
            let url = `/pos/search-restaurant-products?search=${encodeURIComponent(search)}`;

            // Добавяме category_id САМО ако има избрана категория
            if (currentCategoryId) {
                url += `&category_id=${currentCategoryId}`;
            }
            // Ако currentCategoryId === null → търсим във ВСИЧКИ ресторантски продукти

            $.get(url)
                .done(function (products) {
                    if (!products || products.length === 0) {
                        $('#productsGrid').html('<div class="col-span-full text-center text-gray-400 py-12">Няма намерени продукти</div>');
                        return;
                    }
                    displayProducts(products);
                })
                .fail(function () {
                    $('#productsGrid').html('<div class="col-span-full text-center text-red-500 py-12">Грешка при търсене</div>');
                });
        }, 300);
    });
}

function changeTable() {
    let newTable = prompt('Въведете номер на маса:', tableNumber);
    if (newTable && !isNaN(newTable)) {
        tableNumber = parseInt(newTable);
        $('#tableNumber').text(tableNumber);
        localStorage.setItem('tableNumber', tableNumber);
    }
}

function loadProducts(categoryId) {
    $.get(`/pos/products-by-category/${categoryId}`).done(products => displayProducts(products));
}

function showAllProducts() {
    currentCategoryId = null;                    // ←←← МНОГО ВАЖНО!

    $.get('/pos/all-restaurant-products')
        .done(function (products) {
            displayProducts(products);

            // Премахваме active клас от всички категории
            $('.category-btn').removeClass('bg-primary-600 text-white shadow-md')
                .addClass('bg-gray-100');
        })
        .fail(function () {
            $('#productsGrid').html('<div class="col-span-full text-center text-red-500 py-12">Грешка при зареждане</div>');
        });
}

// Проверка дали мерната единица е дробна (кг, л, м)
function isFractionalUnit(unit) {
    const fractionalUnits = ['кг', 'kg', 'л', 'l', 'L', 'м', 'm', 'м²', 'sqm', 'кв.м'];
    return fractionalUnits.includes(unit?.toLowerCase());
}

function displayProducts(products) {
    let html = '';
    if (!Array.isArray(products)) products = [];

    if (products.length === 0) {
        html = '<div class="col-span-full text-center text-gray-400 py-12">Няма продукти в тази категория</div>';
    } else {
        products.forEach(p => {
            let availableQty = parseFloat(p.available_quantity) || 0;
            let stockClass = '';
            let stockBadge = '';
            let isFractional = isFractionalUnit(p.unit);

            if (availableQty <= 0) {
                stockClass = 'opacity-50 grayscale';
                stockBadge = '<div class="text-xs text-red-500 mt-1">✗ Няма наличност</div>';
            } else if (availableQty < 5) {
                stockBadge = `<div class="text-xs text-orange-500 mt-1">⚠️ Остава: ${availableQty.toFixed(3)}</div>`;
            } else {
                stockBadge = `<div class="text-xs text-green-500 mt-1">✓ Налично: ${availableQty.toFixed(3)}</div>`;
            }

            // ⭐ ВЗИМАМЕ ИКОНАТА ОТ ГЛОБАЛНАТА ФУНКЦИЯ ⭐
            const icon = window.getProductIcon(p.name);

            html += `
                <div class="product-card bg-white border-2 border-gray-100 hover:border-primary-500 rounded-xl md:rounded-2xl lg:rounded-3xl p-2 md:p-3 lg:p-4 cursor-pointer transition-all active:scale-95 text-center ${stockClass}"
                     data-product-id="${p.id}"
                     data-product-name="${p.name.replace(/'/g, "\\'")}"
                     data-price="${p.discounted_price}"
                     data-original-price="${p.base_price}"
                     data-discount="${p.discount_percent}"
                     data-available="${availableQty}"
                     data-unit="${p.unit || 'бр.'}"
                     data-is-fractional="${isFractional}">
                    
                    <!-- Икона - responsive размер -->
                    <div class="text-3xl md:text-4xl lg:text-5xl mb-2 md:mb-3">${icon}</div>
                    
                    <!-- Име на продукта -->
                    <div class="font-semibold text-sm md:text-base lg:text-lg leading-tight mb-1 line-clamp-2">${escapeHtml(p.name)}</div>
                    
                    <!-- Цена -->
                    <div class="text-lg md:text-xl lg:text-2xl font-bold text-primary-600">${parseFloat(p.discounted_price).toFixed(2)} €</div>
                    
                    <!-- Стара цена при отстъпка -->
                    ${p.discount_percent > 0 ? `<div class="text-xs md:text-sm text-red-500 line-through">${parseFloat(p.base_price).toFixed(2)} €</div>` : ''}
                    
                    <!-- Бейдж за наличност -->
                    ${stockBadge}
                    
                    <div class="hidden sm:block text-xs text-gray-400 mt-1">${p.unit || 'бр.'}</div>
                </div>
            `;
        });
    }
    $('#productsGrid').html(html);
    attachProductEvents();
}

// function getProductIcon(name) {
//     const icons = {
//         'пица': '🍕', 'домати': '🍅', 'салата': '🥗', 'супа': '🥣',
//         'месо': '🍖', 'риба': '🐟', 'десерт': '🍰', 'кафе': '☕',
//         'чай': '🍵', 'вода': '💧', 'сок': '🥤', 'бира': '🍺', 'вино': '🍷'
//     };
//     for (let [key, icon] of Object.entries(icons)) {
//         if (name.toLowerCase().includes(key)) return icon;
//     }
//     return '🍽️';
// }

function attachProductEvents() {
    $('.product-card').off('click').on('click', function () {
        const available = parseFloat($(this).data('available'));
        const isFractional = $(this).data('is-fractional') === true || $(this).data('is-fractional') === 'true';

        if (available <= 0) {
            alert('Този продукт не е наличен в момента!');
            return;
        }

        selectedProduct = {
            id: $(this).data('product-id'),
            name: $(this).data('product-name'),
            price: parseFloat($(this).data('price')),
            original_price: parseFloat($(this).data('original-price')),
            discount: parseFloat($(this).data('discount')),
            available: available,
            unit: $(this).data('unit') || 'бр.',
            isFractional: isFractional
        };

        if (isFractional) {
            openQuantityModal();
        } else {
            addToCart({
                id: selectedProduct.id,
                name: selectedProduct.name,
                price: selectedProduct.price,
                original_price: selectedProduct.original_price,
                discount: selectedProduct.discount,
                quantity: 1,
                available: selectedProduct.available
            });
        }
    });
}

function openQuantityModal() {
    $('#modalProductName').text(selectedProduct.name);
    $('#modalUnitPrice').text(selectedProduct.price.toFixed(2) + ' €');
    $('#modalUnit').text(selectedProduct.unit);
    $('#modalAvailable').text(selectedProduct.available.toFixed(3) + ' ' + selectedProduct.unit);
    $('#modalQuantity').val('1');
    updateModalTotal();
    $('#quantityModal').removeClass('hidden');
    setTimeout(() => {
        $('#modalQuantity').focus();
        $('#modalQuantity').select();
    }, 100);
}

function updateModalTotal() {
    let rawValue = $('#modalQuantity').val();
    let quantity = parseQuantityInput(rawValue);
    let total = quantity * selectedProduct.price;

    if (!isNaN(total)) {
        $('#modalTotalPrice').text(total.toFixed(2) + ' €');
    }

    if (quantity > selectedProduct.available) {
        $('#confirmQuantityBtn').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
        $('#modalAvailable').addClass('text-red-600');
    } else {
        $('#confirmQuantityBtn').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        $('#modalAvailable').removeClass('text-red-600');
    }
}

function parseQuantityInput(value) {
    if (!value || value === '') return 0;
    // Замяна на запетая с точка
    let normalized = value.toString().replace(',', '.');
    let parsed = parseFloat(normalized);
    return isNaN(parsed) ? 0 : parsed;
}

function closeQuantityModal() {
    $('#quantityModal').addClass('hidden');
    selectedProduct = null;
}

function confirmQuantity() {
    let rawValue = $('#modalQuantity').val();
    let quantity = parseQuantityInput(rawValue);

    if (quantity <= 0) {
        alert('Моля, въведете валидно количество (пример: 0.500, 1, 1.5, 2.5)');
        $('#modalQuantity').focus();
        return;
    }

    if (quantity > selectedProduct.available) {
        alert(`Няма достатъчна наличност! Налично: ${selectedProduct.available.toFixed(3)} ${selectedProduct.unit}`);
        $('#modalQuantity').focus();
        return;
    }

    addToCart({
        id: selectedProduct.id,
        name: selectedProduct.name,
        price: selectedProduct.price,
        original_price: selectedProduct.original_price,
        discount: selectedProduct.discount,
        quantity: quantity,
        available: selectedProduct.available
    });
    closeQuantityModal();
}

function addToCart(product) {
    // Гарантираме, че всички числови стойности са числа
    const quantity = parseFloat(product.quantity) || 0;
    const price = parseFloat(product.price) || 0;
    const original_price = parseFloat(product.original_price) || price;
    const discount = parseFloat(product.discount) || 0;

    let existing = currentItems.find(item => item.product_id === product.id);
    if (existing) {
        existing.quantity = (parseFloat(existing.quantity) || 0) + quantity;
    } else {
        currentItems.push({
            product_id: product.id,
            product_name: product.name,
            price: price,
            original_price: original_price,
            discount: discount,
            quantity: quantity
        });
    }
    updateCart();
    saveCart();
}


function updateCart() {
    let html = '';
    let subtotal = 0;
    let totalDiscount = 0;
    let itemsCount = 0;

    currentItems.forEach((item, i) => {
        // Превръщаме всички числови стойности в числа
        const quantity = parseFloat(item.quantity) || 0;
        const price = parseFloat(item.price) || 0;
        const original_price = parseFloat(item.original_price) || 0;
        const discount = parseFloat(item.discount) || 0;

        const itemTotal = quantity * price;
        const originalTotal = quantity * original_price;
        const discountAmount = originalTotal - itemTotal;

        subtotal += originalTotal;
        totalDiscount += discountAmount;
        itemsCount += quantity;

        html += `
            <div class="bg-gray-50 rounded-2xl p-4 hover:shadow transition">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="font-semibold text-lg">${escapeHtml(item.product_name)}</div>
                        <div class="text-sm text-gray-500">${quantity.toFixed(3)} × ${price.toFixed(2)} €</div>
                        ${discount > 0 ? `<div class="text-xs text-green-600">-${discount}% отстъпка</div>` : ''}
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

// Помощна функция за защита от XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

window.changeQuantity = (index, change) => {
    if (!currentItems[index]) return;

    let currentQty = parseFloat(currentItems[index].quantity) || 0;
    let newQty = currentQty + change;

    if (newQty < 0.001) { // Позволяваме много малки количества за дробни единици
        currentItems.splice(index, 1);
    } else {
        currentItems[index].quantity = newQty;
    }
    updateCart();
    saveCart(); // Не забравяйте да запазите промените
};

window.removeFromCart = (index) => {
    currentItems.splice(index, 1);
    updateCart();
};

// ==================== МОДАЛ ЗА ПЛАЩАНЕ (ГЛОБАЛНИ ФУНКЦИИ) ====================
let currentTotal = 0;

window.openPaymentModal = function () {
    if (currentItems.length === 0) {
        alert('Няма продукти в поръчката!');
        return;
    }
    currentTotal = parseFloat($('#totalAmount').text());
    $('#modalTotalAmount').text(currentTotal.toFixed(2) + ' €');
    $('#paymentModal').removeClass('hidden');
    hideCashPaymentSection();
};

window.closePaymentModal = function () {
    $('#paymentModal').addClass('hidden');
    hideCashPaymentSection();
};

window.showCashPaymentSection = function () {
    $('#cashPaymentSection').removeClass('hidden');
    $('#cashAmount').val('');
    $('#changeInfo').addClass('hidden');

    setTimeout(() => {
        $('#cashAmount').focus();
    }, 100);

    $('#cashAmount').off('input').on('input', function () {
        let rawValue = $(this).val();
        let paid = parseQuantityInput(rawValue);
        let change = paid - currentTotal;

        if (change >= 0 && paid > 0) {
            $('#changeAmount').text(change.toFixed(2) + ' €');
            $('#changeInfo').removeClass('hidden');
        } else {
            $('#changeInfo').addClass('hidden');
        }
    });
};

window.hideCashPaymentSection = function () {
    $('#cashPaymentSection').addClass('hidden');
    $('#cashAmount').off('input');
};

window.handleCashAmountKeyPress = function (event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        confirmCashPayment();
    }
};

window.confirmCashPayment = function () {
    let rawValue = $('#cashAmount').val();
    let paid = parseQuantityInput(rawValue);

    if (paid < currentTotal) {
        alert('Въведената сума е по-малка от дължимата!');
        return;
    }

    let change = paid - currentTotal;
    window.processPayment('cash', paid, change);  // Използваме window.processPayment
};

// Премахнете старата processPayment функция и използвайте само тази:
window.processPayment = function (method, amountPaid = null, changeAmount = null) {
    let clientId = $('#clientSelect').val();
    let paymentAmount = amountPaid !== null ? amountPaid : currentTotal;
    let change = changeAmount !== null ? changeAmount : 0;

    // Проверка дали имаме валиден cart_id
    if (!currentCartId) {
        alert('Грешка: Липсва ID на текущата поръчка!');
        console.error('currentCartId is missing:', currentCartId);
        return;
    }

    console.log('Sending payment request with:', {
        cart_id: currentCartId,
        storage_object_id: storageObjectId,
        client_id: clientId,
        items_count: currentItems.length,
        payment_method: method
    });

    $.ajax({
        url: '/pos/restaurant-receipt',
        method: 'POST',
        data: {
            cart_id: currentCartId,  // Това беше пропуснато!
            client_id: clientId,
            storage_object_id: storageObjectId,
            items: currentItems.map(item => ({
                product_id: item.product_id,
                quantity: parseFloat(item.quantity) || 0,
                price: parseFloat(item.price) || 0
            })),
            table_number: tableNumber,
            payment_method: method,
            amount_paid: paymentAmount,
            change_amount: change,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            if (res.success) {
                let message = `✅ Поръчка №${res.receipt_number} на маса ${tableNumber} е записана успешно!\n`;
                message += `Плащане: ${method === 'card' ? 'Карта' : 'В брой'}`;
                if (method === 'cash' && change > 0) {
                    message += `\nРесто: ${change.toFixed(2)} €`;
                }
                alert(message);

                // Актуализираме currentCartId с новия ID от отговора
                if (res.new_cart_id) {
                    currentCartId = res.new_cart_id;
                }

                currentItems = [];
                updateCart();
                closePaymentModal();

                // Опционално: презареждане на страницата след успешно плащане
                // location.reload();
            } else {
                alert('❌ Грешка: ' + (res.message || 'Неизвестна грешка'));
            }
        },
        error: function (xhr) {
            console.error('Payment error details:', xhr);
            let errorMsg = 'Възникна грешка при плащането!';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg += '\n' + xhr.responseJSON.message;
            }
            alert(errorMsg);
        }
    });
};


// ОСТАВЕТЕ само тази функция:
window.processPayment = function (method, amountPaid = null, changeAmount = null) {
    let clientId = $('#clientSelect').val();
    let paymentAmount = amountPaid !== null ? amountPaid : currentTotal;
    let change = changeAmount !== null ? changeAmount : 0;

    if (!currentCartId) {
        alert('Грешка: Липсва ID на текущата поръчка!');
        console.error('currentCartId is missing:', currentCartId);
        return;
    }

    console.log('Sending payment request with:', {
        cart_id: currentCartId,
        storage_object_id: storageObjectId,
        client_id: clientId,
        items_count: currentItems.length,
        payment_method: method
    });

    $.ajax({
        url: '/pos/restaurant-receipt',
        method: 'POST',
        data: {
            cart_id: currentCartId,
            client_id: clientId,
            storage_object_id: storageObjectId,
            items: currentItems.map(item => ({
                product_id: item.product_id,
                quantity: parseFloat(item.quantity) || 0,
                price: parseFloat(item.price) || 0
            })),
            table_number: tableNumber,
            payment_method: method,
            amount_paid: paymentAmount,
            change_amount: change,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            if (res.success) {
                let message = `✅ Поръчка №${res.receipt_number} на маса ${tableNumber} е записана успешно!\n`;
                message += `Плащане: ${method === 'card' ? 'Карта' : 'В брой'}`;
                if (method === 'cash' && change > 0) {
                    message += `\nРесто: ${change.toFixed(2)} €`;
                }
                alert(message);

                if (res.new_cart_id) {
                    currentCartId = res.new_cart_id;
                }

                currentItems = [];
                updateCart();
                closePaymentModal();

                // Презареждане след успешно плащане
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('❌ Грешка: ' + (res.message || 'Неизвестна грешка'));
            }
        },
        error: function (xhr) {
            console.error('Payment error details:', xhr);
            let errorMsg = 'Възникна грешка при плащането!';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg += '\n' + xhr.responseJSON.message;
            }
            alert(errorMsg);
        }
    });
};

// Закачане на бутона за плащане
$('#checkoutBtn').off('click').on('click', function () {
    openPaymentModal();
});

// Известяваме, че скриптът е зареден
console.log('Restaurant POS script loaded');