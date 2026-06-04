<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShoppingMallPosController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

// Публични маршрути
Route::get('/login', function () {
    return redirect()->to('/admin/login');
})->name('login');

Route::get('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');

// Начална страница
Route::get('/', [HomeController::class, 'index'])->name('home');

// ========================================
// POS МАРШРУТИ
// ========================================
Route::prefix('pos')->middleware(['auth'])->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos.index');
    Route::get('/search', [PosController::class, 'searchProducts'])->name('pos.search');
    Route::get('/stock', [PosController::class, 'getVariantStock'])->name('pos.stock');
    Route::post('/receipt', [PosController::class, 'createReceipt'])->name('pos.receipt');
    Route::get('/carts', [PosController::class, 'getCarts'])->name('pos.carts');
    Route::post('/cart/new', [PosController::class, 'createNewCart'])->name('pos.cart.new');
    Route::get('/cart/{cartId}', [PosController::class, 'getCart'])->name('pos.cart.show');
    Route::put('/cart/{cartId}', [PosController::class, 'updateCart'])->name('pos.cart.update');
    Route::post('/cart/{cartId}/add', [PosController::class, 'addToCart'])->name('pos.cart.add');
    Route::delete('/cart/{cartId}/remove', [PosController::class, 'removeFromCart'])->name('pos.cart.remove');
    Route::delete('/cart/{cartId}', [PosController::class, 'deleteCart'])->name('pos.cart.delete');
    Route::post('/add-to-cart-virtual', [PosController::class, 'addToCartVirtual'])->name('pos.cart.add-virtual');
});

// Restaurant POS маршрути
Route::middleware(['auth'])->group(function () {
    Route::get('/restaurant-pos', [PosController::class, 'restaurantPos'])->name('restaurant.pos');
    Route::get('/pos/all-products', [PosController::class, 'allProducts']);
    Route::get('/pos/products-by-category/{categoryId}', [PosController::class, 'productsByCategory'])->name('pos.products-by-category');
    Route::post('/pos/restaurant-receipt', [PosController::class, 'restaurantReceipt'])->name('pos.restaurant-receipt');
});

// ========================================
// СТОКОВИ МАРШРУТИ
// ========================================
Route::middleware(['auth'])->prefix('stocks')->group(function () {
    Route::get('/', [StockController::class, 'index'])->name('stocks.index');
    Route::post('/adjust', [StockController::class, 'adjust'])->name('stocks.adjust');
    Route::get('/low', [StockController::class, 'lowStock'])->name('stocks.low');
});

Route::get('/stock/check', [StockController::class, 'check'])->name('stock.check');

// ========================================
// ДОКЛАДИ (с middleware за ограничаване на касиерите)
// ========================================
Route::middleware(['auth', 'no.cashier'])->prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('/product-sales', [ReportController::class, 'productSales'])->name('reports.product-sales');
    Route::get('/client-sales', [ReportController::class, 'clientSales'])->name('reports.client-sales');
    Route::get('/cashier-sales', [ReportController::class, 'cashierSales'])->name('reports.cashier-sales');
    Route::get('/staff-sales', [ReportController::class, 'staffSales'])->name('reports.staff-sales');
    Route::get('/monthly-sales', [ReportController::class, 'monthlySales'])->name('reports.monthly-sales');
    Route::get('/product-purchases', [ReportController::class, 'productPurchases'])->name('reports.product-purchases');
    Route::get('/profit-analysis', [ReportController::class, 'profitAnalysis'])->name('reports.profit-analysis');
    Route::get('/stock-status', [ReportController::class, 'stockStatus'])->name('reports.stock-status');
    Route::get('/hourly-sales', [ReportController::class, 'hourlySales'])->name('reports.hourly-sales');
    Route::get('/top-products', [ReportController::class, 'topProducts'])->name('reports.top-products');
    Route::get('/customer-analysis', [ReportController::class, 'customerAnalysis'])->name('reports.customer-analysis');
    Route::get('/daily-turnover', [ReportController::class, 'dailyTurnover'])->name('reports.daily-turnover');
    Route::get('/monthly-profit', [ReportController::class, 'monthlyProfit'])->name('reports.monthly-profit');
    Route::get('/low-stock', [ReportController::class, 'lowStockAlert'])->name('reports.low-stock');
    Route::get('/payment-methods', [ReportController::class, 'paymentMethods'])->name('reports.payment-methods');
    Route::get('/shopping-mall-sales', [ReportController::class, 'shoppingMallSales'])->name('reports.shopping-mall-sales');
    Route::get('/kiosk-sales', [ReportController::class, 'kioskSales'])->name('reports.kiosk-sales');
    Route::get('/shopping-mall-product-sales', [ReportController::class, 'shoppingMallProductSales'])->name('reports.shopping-mall-product-sales');
});

// Печатни форми
Route::middleware(['auth'])->prefix('print')->group(function () {
    Route::get('/receipt/{id}', [PrintController::class, 'printReceipt'])->name('print.receipt');
    Route::get('/invoice/{id}', [PrintController::class, 'printInvoice'])->name('print.invoice');
});

// ========================================
// SHOPPING MALL POS МАРШРУТИ - ОПРОСТЕНИ
// ========================================
Route::middleware(['auth'])->prefix('shopping-mall')->group(function () {

    // Всички маршрути са достъпни за логнати потребители
    Route::get('/', [App\Http\Controllers\ShoppingMallPosController::class, 'index'])->name('shopping-mall.pos');
    Route::get('/kiosk', [App\Http\Controllers\ShoppingMallPosController::class, 'kioskIndex'])->name('shopping-mall.kiosk');
    Route::get('/sessions', [App\Http\Controllers\ShoppingMallPosController::class, 'getSessions']);
    Route::get('/sessions/{token}', [App\Http\Controllers\ShoppingMallPosController::class, 'getSession']);
    Route::get('/sessions/{token}/summary', [App\Http\Controllers\ShoppingMallPosController::class, 'getSessionSummary']);
    Route::get('/search-products', [App\Http\Controllers\ShoppingMallPosController::class, 'searchProducts']);

    Route::post('/sessions', [App\Http\Controllers\ShoppingMallPosController::class, 'createSession']);
    Route::post('/items', [App\Http\Controllers\ShoppingMallPosController::class, 'addItem']);
    Route::post('/payment', [App\Http\Controllers\ShoppingMallPosController::class, 'processPayment']);

    Route::put('/items/{itemId}', [App\Http\Controllers\ShoppingMallPosController::class, 'updateItemQuantity']);
    Route::put('/sessions/{sessionId}/note', [App\Http\Controllers\ShoppingMallPosController::class, 'updateSessionNote']);

    Route::delete('/items/{itemId}', [App\Http\Controllers\ShoppingMallPosController::class, 'removeItem']);
    Route::delete('/sessions/{sessionId}', [App\Http\Controllers\ShoppingMallPosController::class, 'cancelSession']);
});

// Fallback
Route::fallback(function () {
    return redirect('/');
});