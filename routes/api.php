<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])
            ->name('api.products.index');

        Route::get('/stock', [ProductController::class, 'stock_index'])
            ->name('api.products.stock');
    });

    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])
            ->name('api.orders.index');

        Route::post('/', [OrderController::class, 'store'])
            ->name('api.orders.store');

        Route::get('/{id}', [OrderController::class, 'show'])
            ->whereNumber('id')
            ->name('api.orders.show');
    });
});
