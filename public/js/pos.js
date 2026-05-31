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
        return parseFloat(qty) || 0;
    },

    formatMoney(amount) {
        return amount.toFixed(2) + ' €';
    },

    calculateVat(total) {
        return total * 0.20;
    },

    isFractionalUnit(unit) {
        const fractionalUnits = ['кг', 'kg', 'л', 'l', 'L', 'м', 'm', 'м²', 'sqm', 'кв.м'];
        return fractionalUnits.includes(unit?.toLowerCase());
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
            let unit = item.unit || 'бр.';

            total += itemTotal;
            itemCount += quantity;

            cartHtml += `
            <div class="cart-item p-3 border-b flex justify-between items-center hover:bg-gray-50 transition">
                <div class="flex-1">
                    <div class="font-semibold text-gray-800">${POSUtils.escapeHtml(item.product_name)}</div>
                    <div class="text-sm text-gray-600">${POSUtils.escapeHtml(item.variant_name || 'Стандартен')}</div>
                    <div class="text-sm text-gray-500">${itemPrice.toFixed(2)} € × ${quantity.toFixed(3)} ${unit}</div>
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

        $('#itemsCount').text(itemCount.toFixed(3));
        $('#itemsCountFooter').text(itemCount.toFixed(3));

        let vat = POSUtils.calculateVat(total);
        $('#totalAmount').text(POSUtils.formatMoney(total));
        $('#totalVat').text(POSUtils.formatMoney(vat));

        if (this.pos.currentItems.length === 0) {
            $('#checkoutBtn').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
        } else {
            $('#checkoutBtn').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        }
    }

    updateCartBadge() {
        let itemCount = this.pos.currentItems.reduce((sum, item) => sum + POSUtils.parseQuantity(item.quantity), 0);
        $(`.cart-tab[data-cart-id="${this.pos.currentCartId}"] .cart-badge`).text(itemCount.toFixed(3));
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
        $('#loadingIndicator').removeClass('hidden');
        $('#productsGrid').addClass('hidden');
        $('#scrollHint').addClass('hidden');

        $.get('/pos/search', { search: '' })
            .done((products) => this.displayResults(products))
            .fail(() => {});
    }

    searchProducts(search) {
        $.get('/pos/search', { search: search })
            .done((products) => {
                const exactMatch = this.findExactBarcodeMatch(products, search);

                if (exactMatch && exactMatch.variants && exactMatch.variants.length > 0) {
                    const variant = exactMatch.variants[0];
                    const unit = exactMatch.unit_of_measure?.symbol || 'бр.';

                    if (POSUtils.isFractionalUnit(unit)) {
                        window.selectedProduct = {
                            variantId: variant.id,
                            productName: exactMatch.name,
                            variantName: (variant.color?.name || '') + ' ' + (variant.size?.name || ''),
                            price: variant.final_price,
                            unit: unit,
                            decimalPlaces: exactMatch.unit_of_measure?.decimal_places || 3
                        };
                        openQuantityModal();
                    } else {
                        this.pos.addToCartWithQuantity(
                            variant.id,
                            exactMatch.name,
                            (variant.color?.name || '') + ' ' + (variant.size?.name || ''),
                            variant.final_price,
                            1,
                            unit,
                            exactMatch.unit_of_measure?.decimal_places || 0
                        );
                    }

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
            .fail(() => {});
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
                                data-price="${variant.final_price}"
                                data-unit="${product.unit_of_measure?.symbol || 'бр.'}"
                                data-decimal-places="${product.unit_of_measure?.decimal_places || 0}">
                                <div class="font-semibold text-gray-800">${POSUtils.escapeHtml(product.name)}</div>
                                <div class="text-sm text-gray-600">${POSUtils.escapeHtml((variant.color?.name || '') + ' ' + (variant.size?.name || ''))}</div>
                                <div class="text-xs text-gray-400">Артикул: ${POSUtils.escapeHtml(product.sku)}</div>
                                <div class="text-xs text-gray-400">Мерна единица: ${product.unit_of_measure?.symbol || 'бр.'}</div>
                                ${barcodeInfo}
                                <div class="text-lg font-bold text-primary-600 mt-2">${POSUtils.parsePrice(variant.final_price).toFixed(2)} €</div>
                            </div>
                        `;
                    });
                } else {
                    let barcodeInfo = '';
                    if (product.barcodes && product.barcodes.length > 0) {
                        let primaryBarcode = product.barcodes.find(b => b.is_primary);
                        if (primaryBarcode) {
                            barcodeInfo = `<div class="text-xs text-gray-400">Баркод: ${primaryBarcode.barcode}</div>`;
                        }
                    }

                    productsHtml += `
                    <div class="product-card bg-white border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-primary-500 transition"
                         data-variant-id="product_${product.id}"
                         data-product-id="${product.id}"
                         data-product-name="${POSUtils.escapeHtml(product.name)}"
                         data-variant-name=""
                         data-price="${product.base_price}"
                         data-unit="${product.unit_of_measure?.symbol || 'бр.'}"
                         data-decimal-places="${product.unit_of_measure?.decimal_places || 0}">
                        <div class="font-semibold text-gray-800">${POSUtils.escapeHtml(product.name)}</div>
                        <div class="text-xs text-gray-400">Артикул: ${POSUtils.escapeHtml(product.sku)}</div>
                        <div class="text-xs text-gray-400">Мерна единица: ${product.unit_of_measure?.symbol || 'бр.'}</div>
                        ${barcodeInfo}
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
            let productId = $card.data('product-id');
            let productName = $card.data('product-name');
            let variantName = $card.data('variant-name');
            let price = $card.data('price');
            let unit = $card.data('unit') || 'бр.';
            let decimalPlaces = $card.data('decimal-places') || 0;

            if (!variantId && productId) {
                variantId = 'product_' + productId;
            }

            if (POSUtils.isFractionalUnit(unit)) {
                window.selectedProduct = {
                    variantId: variantId,
                    productId: productId,
                    productName: productName,
                    variantName: variantName,
                    price: price,
                    unit: unit,
                    decimalPlaces: decimalPlaces
                };
                openQuantityModal();
            } else {
                this.pos.addToCartWithQuantity(
                    variantId,
                    productName,
                    variantName,
                    price,
                    1,
                    unit,
                    decimalPlaces
                );
            }

            $card.addClass('bg-green-50');
            setTimeout(() => $card.removeClass('bg-green-50'), 300);
        });
    }
}

// ============================================
// МОДУЛ: Модал за количество
// ============================================
function openQuantityModal() {
    if (!window.selectedProduct) return;

    let unit = window.selectedProduct.unit || 'кг';
    $('#modalProductName').text(window.selectedProduct.productName + (window.selectedProduct.variantName ? ' - ' + window.selectedProduct.variantName : ''));
    $('#modalUnitPrice').text(parseFloat(window.selectedProduct.price).toFixed(2) + ' €');
    $('#modalUnitSymbol').text(unit);
    $('#modalQuantity').val('');

    updateModalTotal();
    $('#quantityModal').removeClass('hidden');

    setTimeout(() => {
        $('#modalQuantity').focus();
    }, 100);
}

function parseQuantityInput(value) {
    if (!value) return 0;
    let normalized = value.replace(',', '.');
    let parsed = parseFloat(normalized);
    return isNaN(parsed) ? 0 : parsed;
}

function updateModalTotal() {
    let rawValue = $('#modalQuantity').val();
    let quantity = parseQuantityInput(rawValue);
    let price = window.selectedProduct.price;
    let total = quantity * price;

    $('#modalTotalPrice').text(total.toFixed(2) + ' €');
}

function closeQuantityModal() {
    $('#quantityModal').addClass('hidden');
    window.selectedProduct = null;
}

$(document).ready(function () {
    $('#modalQuantity').on('input', function () {
        updateModalTotal();
    });

    $('#modalQuantity').on('keypress', function (e) {
        const allowed = /[0-9.,]/;
        if (!allowed.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'Tab' && e.key !== 'Enter') {
            e.preventDefault();
        }
    });

    $('#modalQuantity').on('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#confirmQuantityBtn').click();
        }
    });

    $('#confirmQuantityBtn').click(() => {
        if (!window.selectedProduct) return;

        let rawValue = $('#modalQuantity').val();
        let quantity = parseQuantityInput(rawValue);

        if (quantity <= 0) {
            alert('Моля, въведете валидно количество (пример: 0.500, 1.250, 2,5)');
            $('#modalQuantity').focus();
            return;
        }

        window.POSInstance.addToCartWithQuantity(
            window.selectedProduct.variantId,
            window.selectedProduct.productName,
            window.selectedProduct.variantName,
            window.selectedProduct.price,
            quantity,
            window.selectedProduct.unit,
            window.selectedProduct.decimalPlaces
        );

        closeQuantityModal();
    });

    $('#cancelQuantityBtn').click(() => {
        closeQuantityModal();
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && !$('#quantityModal').hasClass('hidden')) {
            closeQuantityModal();
        }
    });
});

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

        setTimeout(() => {
            const cashInput = document.getElementById('cashAmount');
            if (cashInput) {
                cashInput.focus();
                cashInput.select();
            }
        }, 100);

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
                amount_paid: parseFloat(amountPaid),
                change_amount: parseFloat(changeAmount),
                items: this.pos.currentItems.map(item => ({
                    variant_id: item.variant_id,
                    quantity: item.quantity,
                    unit_price: item.price
                }))
            },
            headers: {
                'X-CSRF-TOKEN': this.pos.config.csrfToken
            },
            success: (response) => {
                if (response.success) {
                    let message = `✅ Продажбата е успешна!\nНомер: ${response.receipt_number || '—'}\n`;
                    if (paymentMethod === 'cash') {
                        message += `Получено: ${POSUtils.formatMoney(amountPaid)}\nРесто: ${POSUtils.formatMoney(changeAmount)}`;
                    } else {
                        message += `Платено с карта`;
                    }
                    alert(message);
                    location.reload();
                } else {
                    alert('❌ Грешка: ' + (response.message || 'Неизвестна грешка'));
                }
            },
            error: (xhr) => {
                if (xhr.status === 419) {
                    alert('CSRF Token изтече. Натисни F5 и опитай отново.');
                } else if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors || xhr.responseJSON;
                    alert('Валидационна грешка: ' + JSON.stringify(errors));
                } else if (xhr.status === 404) {
                    alert('404 - Route не е намерен. Изпълни php artisan route:clear');
                } else {
                    alert('Грешка ' + xhr.status + ': ' + (xhr.responseJSON?.message || xhr.statusText));
                }
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

    addToCart(variantId, productName, variantName, price, unit = 'бр.', decimalPlaces = 0) {
        this.addToCartWithQuantity(variantId, productName, variantName, price, 1, unit, decimalPlaces);
    }

    addToCartWithQuantity(variantId, productName, variantName, price, quantity, unit = 'бр.', decimalPlaces = 0) {
        let isVirtual = typeof variantId === 'string' && variantId.startsWith('product_');

        if (isVirtual) {
            $.post('/pos/add-to-cart-virtual', {
                product_id: variantId.replace('product_', ''),
                storage_object_id: this.storageObjectId,
                quantity: quantity,
                unit_price: price,
                unit: unit,
                decimal_places: decimalPlaces,
                _token: this.config.csrfToken
            }).done((response) => {
                if (response.success) {
                    this.currentItems.push({
                        variant_id: variantId,
                        product_name: productName,
                        variant_name: variantName,
                        price: POSUtils.parsePrice(price),
                        quantity: POSUtils.parseQuantity(quantity),
                        total: POSUtils.parsePrice(price) * POSUtils.parseQuantity(quantity),
                        unit: unit,
                        decimal_places: decimalPlaces
                    });
                    this.saveCart().then(() => {
                        this.cartUI.updateCartDisplay();
                        this.cartUI.updateCartBadge();
                        $('#searchInput').focus();
                    });
                } else {
                    alert('Грешка: ' + response.message);
                }
            });
            return;
        }

        $.get(`/pos/stock?variant_id=${variantId}&storage_object_id=${this.storageObjectId}`)
            .done((stockData) => {
                let existingItem = this.currentItems.find(item => item.variant_id === variantId);
                let numericPrice = POSUtils.parsePrice(price);
                let numericQuantity = POSUtils.parseQuantity(quantity);

                if (existingItem) {
                    let newQuantity = existingItem.quantity + numericQuantity;
                    if (newQuantity > stockData.available) {
                        alert('Няма достатъчна наличност! Оставащи: ' + stockData.available.toFixed(3));
                        return;
                    }
                    existingItem.quantity = newQuantity;
                    existingItem.total = existingItem.quantity * existingItem.price;
                } else {
                    if (numericQuantity > stockData.available) {
                        alert('Няма достатъчна наличност! Оставащи: ' + stockData.available.toFixed(3));
                        return;
                    }
                    this.currentItems.push({
                        variant_id: variantId,
                        product_name: productName,
                        variant_name: variantName,
                        price: numericPrice,
                        quantity: numericQuantity,
                        total: numericPrice * numericQuantity,
                        unit: unit,
                        decimal_places: decimalPlaces
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
        this.productsManager.attachProductClickEvents();

        $(document).on('click', '.cart-tab', (e) => {
            this.loadCart($(e.currentTarget).data('cart-id'));
        });

        const $searchInput = $('#searchInput');
        const $clearBtn = $('#clearSearchBtn');

        $(document).on('click', function (e) {
            const target = $(e.target);
            const isInput = target.is('input, select, button') || target.closest('input, select, button').length;
            const isInModal = target.closest('#paymentModal').length || target.closest('#quantityModal').length;
            const isProductCard = target.closest('.product-card').length;

            if (!isInput && !isInModal && !isProductCard) {
                $searchInput.focus();
            }
        });

        $searchInput.on('input', function () {
            if ($(this).val().length > 0) {
                $clearBtn.removeClass('hidden');
            } else {
                $clearBtn.addClass('hidden');
            }
        });

        $clearBtn.on('click', () => {
            $searchInput.val('');
            $clearBtn.addClass('hidden');
            $searchInput.focus();
            this.productsManager.loadAllProducts();
        });

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

        $('#checkoutBtn').click(() => this.paymentManager.openModal());

        window.selectPaymentMethod = (method) => this.paymentManager.selectPaymentMethod(method);
        window.showCashPayment = () => this.paymentManager.showCashPayment();
        window.confirmCashPayment = () => this.paymentManager.confirmCashPayment();
        window.hideCashPayment = () => this.paymentManager.hideCashPayment();
        window.closePaymentModal = () => this.paymentManager.closeModal();

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

window.POS = POS;