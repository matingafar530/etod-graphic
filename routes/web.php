<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminPrintJobController;
use App\Http\Controllers\AdminPrintTemplateController;
use App\Http\Controllers\AdminProductVariantController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductImageController;
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
        Route::patch('/print-jobs/{job}/status', [AdminPrintJobController::class, 'status'])->name('print-jobs.status');
        Route::post('/print-jobs/{job}/file', [AdminPrintJobController::class, 'uploadFile'])->name('print-jobs.file');
        Route::get('/print-jobs/{job}/file', [AdminPrintJobController::class, 'downloadFile'])->name('print-jobs.file.download');
        Route::get('/print-jobs/{job}/customer-design', [AdminPrintJobController::class, 'customerDesign'])->name('print-jobs.customer-design');
        Route::get('/inventory', [AdminController::class, 'inventory'])->name('inventory');
        Route::patch('/inventory/{variant}', [AdminController::class, 'adjustInventory'])->name('inventory.adjust');
        Route::get('/inventory/movements', [AdminController::class, 'inventoryMovements'])->name('inventory.movements');
        Route::get('/products', [AdminController::class, 'products'])->name('products');
        Route::get('/products/create', [AdminController::class, 'createProduct'])->name('products.create');
        Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
        Route::get('/products/{product}/edit', [AdminController::class, 'editProduct'])->name('products.edit');
        Route::patch('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
        Route::get('/products/{product}/archive', [AdminController::class, 'confirmArchiveProduct'])->name('products.archive.confirm');
        Route::patch('/products/{product}/archive', [AdminController::class, 'archiveProduct'])->name('products.archive');
        Route::get('/products/{product}/variants/create', [AdminProductVariantController::class, 'create'])->name('variants.create');
        Route::post('/products/{product}/variants', [AdminProductVariantController::class, 'store'])->name('variants.store');
        Route::get('/variants/{variant}/edit', [AdminProductVariantController::class, 'edit'])->name('variants.edit');
        Route::patch('/variants/{variant}', [AdminProductVariantController::class, 'update'])->name('variants.update');
        Route::get('/variants/{variant}/print-template/create', [AdminPrintTemplateController::class, 'create'])->name('print-templates.create');
        Route::post('/variants/{variant}/print-template', [AdminPrintTemplateController::class, 'store'])->name('print-templates.store');
        Route::get('/print-templates/{template}/edit', [AdminPrintTemplateController::class, 'edit'])->name('print-templates.edit');
        Route::patch('/print-templates/{template}', [AdminPrintTemplateController::class, 'update'])->name('print-templates.update');
        Route::get('/print-templates/{template}/image', [AdminPrintTemplateController::class, 'image'])->name('print-templates.image');
        Route::post('/products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
        Route::delete('/product-images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
    });
}

Route::get('/product-images/{image}', [ProductImageController::class, 'show'])->name('products.images.show');
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
