<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Публични маршрути (не изискват автентикация)
Route::get('/login', function () {
    return redirect()->to('/admin/login');
})->name('login');

Route::get('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');

// Начална страница (изисква автентикация)
Route::get('/', [HomeController::class, 'index'])->name('home');

// ========================================
// POS МАРШРУТИ
// ========================================
Route::prefix('pos')->middleware(['auth'])->group(function () {
    // Основни POS маршрути
    Route::get('/', [PosController::class, 'index'])->name('pos.index');
    Route::get('/search', [PosController::class, 'searchProducts'])->name('pos.search');
    Route::get('/stock', [PosController::class, 'getVariantStock'])->name('pos.stock');
    Route::post('/receipt', [PosController::class, 'createReceipt'])->name('pos.receipt');
    
    // Маршрути за активни колички
    Route::get('/carts', [PosController::class, 'getCarts'])->name('pos.carts');
    Route::post('/cart/new', [PosController::class, 'createNewCart'])->name('pos.cart.new');
    Route::get('/cart/{cartId}', [PosController::class, 'getCart'])->name('pos.cart.show');
    Route::put('/cart/{cartId}', [PosController::class, 'updateCart'])->name('pos.cart.update');
    Route::post('/cart/{cartId}/add', [PosController::class, 'addToCart'])->name('pos.cart.add');
    Route::delete('/cart/{cartId}/remove', [PosController::class, 'removeFromCart'])->name('pos.cart.remove');
    Route::delete('/cart/{cartId}', [PosController::class, 'deleteCart'])->name('pos.cart.delete');
});

// ========================================
// СТОКОВИ МАРШРУТИ
// ========================================
Route::middleware(['auth'])->prefix('stocks')->group(function () {
    Route::get('/', [StockController::class, 'index'])->name('stocks.index');
    Route::post('/adjust', [StockController::class, 'adjust'])->name('stocks.adjust');
    Route::get('/low', [StockController::class, 'lowStock'])->name('stocks.low');
});

// Публична проверка на наличност (за POS)
Route::get('/stock/check', [StockController::class, 'check'])->name('stock.check');

// ========================================
// ПОКУПКИ (ПРИДОБИВАНЕ НА СТОКИ)
// ========================================
Route::middleware(['auth'])->prefix('purchases')->group(function () {
    Route::get('/', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::delete('/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
});

// ========================================
// ФИЛМЕНТ АДМИНИСТРАТИВЕН ПАНЕЛ
// ========================================
// Filament се зарежда автоматично на /admin
// НЕ добавяйте маршрути, които започват с /admin, за да няма конфликти

// ========================================
// FALLBACK (опционално)
// ========================================
Route::fallback(function () {
    return redirect('/');
});