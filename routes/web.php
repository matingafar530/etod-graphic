<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

if (app()->environment(['local', 'testing'])) {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [AdminController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [AdminController::class, 'updateStatus'])->name('orders.status');
        Route::get('/print-queue', [AdminController::class, 'printQueue'])->name('print-queue');
        Route::get('/inventory', [AdminController::class, 'inventory'])->name('inventory');
        Route::patch('/inventory/{variant}', [AdminController::class, 'adjustInventory'])->name('inventory.adjust');
    });
}

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/products/{product:slug}', [StorefrontController::class, 'show'])->name('products.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/orders/{order:order_number}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{order}/mock-payment', [CheckoutController::class, 'mockPay'])->name('orders.mock-payment');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
Route::get('/uploads/{upload}', [UploadController::class, 'show'])->name('uploads.show');
