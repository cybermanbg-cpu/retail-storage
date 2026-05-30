// ============================================
// МОДУЛ: Помощни функции
// ============================================
const POSUtils = {
    escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    },

    parsePrice(price) {
        return parseFloat(price) || 0;
    },

    parseQuantity(qty) {
        return parseInt(qty) || 0;
    },

    formatMoney(amount) {
        return amount.toFixed(2) + ' €';
    },

    calculateVat(total) {
        return total * 0.20;
    }
};

// ============================================
// МОДУЛ: Управление на количката (UI)
// ============================================
class CartUI {
    constructor(posInstance) {
        this.pos = posInstance;
    }

    updateCartDisplay() {
        let cartHtml = '';
        let total = 0;
        let itemCount = 0;

        this.pos.currentItems.forEach((item, index) => {
            let itemTotal = POSUtils.parsePrice(item.total);
            let itemPrice = POSUtils.parsePrice(item.price);
            let quantity = POSUtils.parseQuantity(item.quantity);

            total += itemTotal;
            itemCount += quantity;

         cartHtml += `
            <div class="cart-item p-3 border-b flex justify-between items-center hover:bg-gray-50 transition">
                <div class="flex-1">
                    <div class="font-semibold text-gray-800">${POSUtils.escapeHtml(item.product_name)}</div>
                    <div class="text-sm text-gray-600">${POSUtils.escapeHtml(item.variant_name || 'Стандартен')}</div>
                    <div class="text-sm text-gray-500">${itemPrice.toFixed(2)} € × ${quantity}</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-primary-600">${itemTotal.toFixed(2)} €</div>
                    <button onclick="window.POSInstance.removeFromCart(${index})" 
                            class="delete-item-btn mt-2 w-full flex items-center justify-center gap-1 text-red-500 hover:text-white hover:bg-red-500 text-sm py-1 px-2 rounded-lg transition-all duration-200">
                        <i class="fas fa-trash-alt text-xs"></i>
                        <span>Премахни</span>
                    </button>
                </div>
            </div>
`;
        });

        if (this.pos.currentItems.length === 0) {
            cartHtml = '<div class="text-center text-gray-400 py-8 text-sm">' +
                '<i class="fas fa-shopping-cart text-4xl mb-2 block opacity-50"></i>' +
                '<p>Количката е празна</p>' +
                '<p class="text-xs mt-1">Добавете продукти от лявата страна</p>' +
                '</div>';
        }

        $('#cartItems').html(cartHtml);

        // Актуализиране на броя артикули
        $('#itemsCount').text(itemCount);
        $('#itemsCountFooter').text(itemCount);

        // Изчисляване на ДДС
        let vat = POSUtils.calculateVat(total);
        $('#totalAmount').text(POSUtils.formatMoney(total));
        $('#totalVat').text(POSUtils.formatMoney(vat));

        // Активиране/деактивиране на бутона
        if (itemCount === 0) {
            $('#checkoutBtn').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
        } else {
            $('#checkoutBtn').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        }
    }

    updateCartBadge() {
        $(`.cart-tab[data-cart-id="${this.pos.currentCartId}"] .cart-badge`).text(this.pos.currentItems.length);
    }
}

// ============================================
// МОДУЛ: Продукти (търсене и показване)
// ============================================
class ProductsManager {
    constructor(posInstance) {
        this.pos = posInstance;
    }

   loadAllProducts() {
    // Покажи индикатора
    $('#loadingIndicator').removeClass('hidden');
    $('#productsGrid').addClass('hidden');
    $('#scrollHint').addClass('hidden');
    
        $.get('/pos/search', { search: '' })
            .done((products) => this.displayResults(products))
            .fail(() => console.error('Грешка при зареждане на продукти'));
    }

    searchProducts(search) {
        $.get('/pos/search', { search: search })
            .done((products) => {
                const exactMatch = this.findExactBarcodeMatch(products, search);

                if (exactMatch && exactMatch.variants && exactMatch.variants.length > 0) {
                    const variant = exactMatch.variants[0];
                    this.pos.addToCart(
                        variant.id,
                        exactMatch.name,
                        (variant.color?.name || '') + ' ' + (variant.size?.name || ''),
                        variant.final_price
                    );
                    const $searchInput = $('#searchInput');
                    const originalValue = $searchInput.val();
                    $searchInput.val('✅ ' + originalValue);
                    setTimeout(() => {
                        $searchInput.val('');
                        $('#clearSearchBtn').addClass('hidden');
                        $searchInput.focus();
                    }, 800);
                    this.showScanSuccess();
                } else {
                    this.displayResults(products);
                }
            })
            .fail(() => console.error('Грешка при търсене на продукти'));
    }

    findExactBarcodeMatch(products, barcode) {
        for (let product of products) {
            if (product.barcodes && product.barcodes.length > 0) {
                const matchedBarcode = product.barcodes.find(b =>
                    b.barcode === barcode || b.barcode === barcode.trim()
                );
                if (matchedBarcode) {
                    return product;
                }
            }
            if (product.sku === barcode || product.sku === barcode.trim()) {
                return product;
            }
        }
        return null;
    }

    showScanSuccess() {
        const searchInput = $('#searchInput');
        searchInput.addClass('scan-success');
        setTimeout(() => {
            searchInput.removeClass('scan-success');
        }, 300);
    }

   displayResults(products) {
    // Скрий индикатора за зареждане
    $('#loadingIndicator').addClass('hidden');
    $('#productsGrid').removeClass('hidden');
    $('#scrollHint').removeClass('hidden');
    
    let productsHtml = '';

        if (products.length === 0) {
            productsHtml = '<div class="col-span-full text-center text-gray-500 py-8">Няма намерени продукти</div>';
        } else {
            products.forEach(product => {
                if (product.variants && product.variants.length > 0) {
                    product.variants.forEach(variant => {
                        let barcodeInfo = '';
                        if (product.barcodes && product.barcodes.length > 0) {
                            let primaryBarcode = product.barcodes.find(b => b.is_primary);
                            if (primaryBarcode) {
                                barcodeInfo = `<div class="text-xs text-gray-400">Баркод: ${primaryBarcode.barcode}</div>`;
                            }
                        }

                        productsHtml += `
                            <div class="product-card bg-white border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-primary-500 transition"
                                 data-variant-id="${variant.id}"
                                 data-product-name="${POSUtils.escapeHtml(product.name)}"
                                 data-variant-name="${POSUtils.escapeHtml((variant.color?.name || '') + ' ' + (variant.size?.name || ''))}"
                                 data-price="${variant.final_price}">
                                <div class="font-semibold text-gray-800">${POSUtils.escapeHtml(product.name)}</div>
                                <div class="text-sm text-gray-600">${POSUtils.escapeHtml((variant.color?.name || '') + ' ' + (variant.size?.name || ''))}</div>
                                <div class="text-xs text-gray-400">Артикул: ${POSUtils.escapeHtml(product.sku)}</div>
                                ${barcodeInfo}
                                <div class="text-lg font-bold text-primary-600 mt-2">${POSUtils.parsePrice(variant.final_price).toFixed(2)} €</div>
                            </div>
                        `;
                    });
                } else {
                    productsHtml += `
                        <div class="product-card bg-white border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-primary-500 transition"
                             data-variant-id="${product.id}"
                             data-product-name="${POSUtils.escapeHtml(product.name)}"
                             data-variant-name=""
                             data-price="${product.base_price}">
                            <div class="font-semibold text-gray-800">${POSUtils.escapeHtml(product.name)}</div>
                            <div class="text-xs text-gray-400">Артикул: ${POSUtils.escapeHtml(product.sku)}</div>
                            <div class="text-lg font-bold text-primary-600 mt-2">${POSUtils.parsePrice(product.base_price).toFixed(2)} €</div>
                        </div>
                    `;
                }
            });
        }

        $('#productsGrid').html(productsHtml);
        this.attachProductClickEvents();
    }

    attachProductClickEvents() {
        $('.product-card').off('click').on('click', (e) => {
            let $card = $(e.currentTarget);
            let variantId = $card.data('variant-id');
            let productName = $card.data('product-name');
            let variantName = $card.data('variant-name');
            let price = $card.data('price');

            this.pos.addToCart(variantId, productName, variantName, price);

            $card.addClass('bg-green-50');
            setTimeout(() => $card.removeClass('bg-green-50'), 300);
        });
    }
}

// ============================================
// МОДУЛ: Плащане
// ============================================
class PaymentManager {
    constructor(posInstance) {
        this.pos = posInstance;
        this.selectedPaymentMethod = null;
    }

    openModal() {
        if (this.pos.currentItems.length === 0) {
            alert('Няма продукти в количката!');
            return;
        }
        let total = POSUtils.parsePrice($('#totalAmount').text());
        $('#modalTotalAmount').text(POSUtils.formatMoney(total));
        $('#paymentModal').removeClass('hidden');
        this.selectedPaymentMethod = null;
        this.hideCashPayment();
    }

    closeModal() {
        $('#paymentModal').addClass('hidden');
        this.hideCashPayment();
        this.selectedPaymentMethod = null;
    }

    showCashPayment() {
        $('#cashPaymentSection').removeClass('hidden');
        $('#cashAmount').val('');
        $('#changeInfo').addClass('hidden');
        this.selectedPaymentMethod = 'cash';

        $('#cashAmount').off('input').on('input', () => {
            let total = POSUtils.parsePrice($('#modalTotalAmount').text());
            let paid = POSUtils.parsePrice($('#cashAmount').val());
            let change = paid - total;

            if (change >= 0) {
                $('#changeAmount').text(POSUtils.formatMoney(change));
                $('#changeInfo').removeClass('hidden');
            } else {
                $('#changeInfo').addClass('hidden');
            }
        });
    }

    hideCashPayment() {
        $('#cashPaymentSection').addClass('hidden');
        $('#cashAmount').off('input');
    }

    selectPaymentMethod(method) {
        if (method === 'card') {
            this.processCardPayment();
        }
    }

    confirmCashPayment() {
        let total = POSUtils.parsePrice($('#modalTotalAmount').text());
        let paid = POSUtils.parsePrice($('#cashAmount').val());

        if (paid < total) {
            alert('Въведената сума е по-малка от дължимата!');
            return;
        }

        let change = paid - total;
        this.processSale('cash', paid, change);
    }

    processCardPayment() {
        let total = POSUtils.parsePrice($('#modalTotalAmount').text());
        if (confirm(`Потвърдете плащане с карта на стойност ${POSUtils.formatMoney(total)}?`)) {
            this.processSale('card', total, 0);
        } else {
            this.closeModal();
        }
    }

    processSale(paymentMethod, amountPaid, changeAmount) {
        let clientId = $('#clientSelect').val();

        $.ajax({
            url: '/pos/receipt',
            method: 'POST',
            data: {
                cart_id: this.pos.currentCartId,
                storage_object_id: this.pos.storageObjectId,
                client_id: clientId,
                payment_method: paymentMethod,
                amount_paid: amountPaid,
                change_amount: changeAmount,
                items: this.pos.currentItems.map(item => ({
                    variant_id: item.variant_id,
                    quantity: item.quantity,
                    unit_price: item.price
                })),
                _token: this.pos.config.csrfToken
            },
            success: (response) => {
                if (response.success) {
                    let message = `✅ Продажбата е успешна!\nНомер: ${response.receipt_number}\n`;
                    if (paymentMethod === 'cash') {
                        message += `Получено: ${POSUtils.formatMoney(amountPaid)}\nРесто: ${POSUtils.formatMoney(changeAmount)}`;
                    } else {
                        message += `Платено с карта: ${POSUtils.formatMoney(amountPaid)}`;
                    }
                    alert(message);
                    location.reload();
                } else {
                    alert('❌ Грешка: ' + response.message);
                }
            },
            error: (xhr) => {
                let errorMsg = 'Възникна грешка при записване на продажбата!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg += '\n' + xhr.responseJSON.message;
                }
                alert(errorMsg);
            }
        });
    }
}

// ============================================
// ОСНОВЕН КЛАС POS
// ============================================
class POS {
    constructor() {
        this.config = null;
        this.currentCartId = null;
        this.storageObjectId = null;
        this.currentItems = [];

        this.cartUI = null;
        this.productsManager = null;
        this.paymentManager = null;
    }

    init(config) {
        this.config = config;
        this.currentCartId = config.currentCartId;
        this.storageObjectId = config.storageObjectId;

        this.cartUI = new CartUI(this);
        this.productsManager = new ProductsManager(this);
        this.paymentManager = new PaymentManager(this);

        this.loadCart(this.currentCartId);
        this.attachEvents();
        this.productsManager.loadAllProducts();
    }

    saveCart() {
        return $.ajax({
            url: `/pos/cart/${this.currentCartId}`,
            method: 'PUT',
            data: {
                items: this.currentItems,
                _token: this.config.csrfToken
            }
        });
    }

    loadCart(cartId) {
        $.get(`/pos/cart/${cartId}`)
            .done((data) => {
                this.currentCartId = data.id;
                this.currentItems = data.items || [];
                $('#currentCartName').text(data.cart_name || data.name);

                if (data.client_id) {
                    $('#clientSelect').val(data.client_id);
                } else {
                    $('#clientSelect').val('');
                }

                $('.cart-tab').removeClass('bg-primary-600 text-white').addClass('bg-gray-200 text-gray-700');
                $(`.cart-tab[data-cart-id="${cartId}"]`).removeClass('bg-gray-200 text-gray-700').addClass('bg-primary-600 text-white');

                this.cartUI.updateCartDisplay();
                this.cartUI.updateCartBadge();
            });
    }

    removeFromCart(index) {
        this.currentItems.splice(index, 1);
        this.saveCart().then(() => {
            this.cartUI.updateCartDisplay();
            this.cartUI.updateCartBadge();
        });
    }

    addToCart(variantId, productName, variantName, price) {
        $.get(`/pos/stock?variant_id=${variantId}&storage_object_id=${this.storageObjectId}`)
            .done((stockData) => {
                let existingItem = this.currentItems.find(item => item.variant_id === variantId);
                let numericPrice = POSUtils.parsePrice(price);

                if (existingItem) {
                    let newQuantity = existingItem.quantity + 1;
                    if (newQuantity > stockData.available) {
                        alert('Няма достатъчна наличност! Оставащи: ' + stockData.available);
                        return;
                    }
                    existingItem.quantity = newQuantity;
                    existingItem.total = existingItem.quantity * existingItem.price;
                } else {
                    if (1 > stockData.available) {
                        alert('Няма наличност за този продукт!');
                        return;
                    }
                    this.currentItems.push({
                        variant_id: variantId,
                        product_name: productName,
                        variant_name: variantName,
                        price: numericPrice,
                        quantity: 1,
                        total: numericPrice,
                    });
                }

                this.saveCart().then(() => {
                    this.cartUI.updateCartDisplay();
                    this.cartUI.updateCartBadge();
                    $('#searchInput').focus();
                });
            });
    }

    attachEvents() {
        // Продукти
        this.productsManager.attachProductClickEvents();

        // Колички табове
        $(document).on('click', '.cart-tab', (e) => {
            this.loadCart($(e.currentTarget).data('cart-id'));
        });

        // Елементи за търсене
        const $searchInput = $('#searchInput');
        const $clearBtn = $('#clearSearchBtn');

        // Задържане на фокуса
        $(document).on('click', function(e) {
            const target = $(e.target);
            const isInput = target.is('input, select, button') || target.closest('input, select, button').length;
            const isInModal = target.closest('#paymentModal').length;
            const isProductCard = target.closest('.product-card').length;

            if (!isInput && !isInModal && !isProductCard) {
                $searchInput.focus();
            }
        });

        // Показване/скриване на бутона за изчистване
        $searchInput.on('input', function() {
            if ($(this).val().length > 0) {
                $clearBtn.removeClass('hidden');
            } else {
                $clearBtn.addClass('hidden');
            }
        });

        // Изчистване на полето
        $clearBtn.on('click', () => {
            $searchInput.val('');
            $clearBtn.addClass('hidden');
            $searchInput.focus();
            this.productsManager.loadAllProducts();
        });

        // Нова количка
        $('#newCartBtn').click(() => {
            $.ajax({
                url: '/pos/cart/new',
                method: 'POST',
                data: {
                    storage_object_id: this.storageObjectId,
                    _token: this.config.csrfToken
                },
                success: (response) => {
                    if (response.success) location.reload();
                    else alert('Грешка: ' + (response.message || 'Неуспешно създаване на количка'));
                }
            });
        });

        // Смяна на клиент
        $('#clientSelect').change(() => {
            $.ajax({
                url: `/pos/cart/${this.currentCartId}`,
                method: 'PUT',
                data: {
                    client_id: $('#clientSelect').val(),
                    _token: this.config.csrfToken
                }
            });
        });

        // Бутон за плащане
        $('#checkoutBtn').click(() => this.paymentManager.openModal());

        // Глобални функции за модала
        window.selectPaymentMethod = (method) => this.paymentManager.selectPaymentMethod(method);
        window.showCashPayment = () => this.paymentManager.showCashPayment();
        window.confirmCashPayment = () => this.paymentManager.confirmCashPayment();
        window.hideCashPayment = () => this.paymentManager.hideCashPayment();
        window.closePaymentModal = () => this.paymentManager.closeModal();

        // Търсене на продукти
        let searchTimeout;
        $searchInput.on('input', (e) => {
            clearTimeout(searchTimeout);
            let search = $(e.target).val();

            if (search.length < 2) {
                this.productsManager.loadAllProducts();
                return;
            }

            searchTimeout = setTimeout(() => {
                this.productsManager.searchProducts(search);
            }, 300);
        });

        // Автоматично добавяне при Enter
        $searchInput.on('keypress', (e) => {
            if (e.which === 13) {
                e.preventDefault();
                let searchValue = $searchInput.val().trim();
                if (searchValue.length > 0) {
                    setTimeout(() => {
                        const firstProduct = $('.product-card').first();
                        if (firstProduct.length === 1) {
                            firstProduct.trigger('click');
                            $searchInput.val('');
                            $searchInput.focus();
                        }
                    }, 300);
                }
            }
        });
    }
}

// Глобална променлива
window.POS = POS;