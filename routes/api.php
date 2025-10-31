<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

/**
 * API routes for the application.
 *
 * These routes handle JSON-based requests from the frontend (Vue SPA).
 * All routes are automatically prefixed with "/api" by Laravel's configuration.
 */


/**
 * ============================================================
 * API Routes
 * ------------------------------------------------------------
 * These routes handle JSON-based communication with the Vue SPA.
 * All routes are automatically prefixed with "/api".
 * ============================================================
 */

Route::middleware('api')->group(function () {

    /* ===== Product Routes ===== */
    Route::prefix('produtos')->group(function () {
        // GET /api/produtos → List or search products
        Route::get('/', [ProductController::class, 'index'])
            ->name('api.products.index');
    });

    /* ===== Order Routes ===== */
    Route::prefix('pedidos')->group(function () {
        // GET /api/pedidos → List all orders
        Route::get('/', [OrderController::class, 'index'])
            ->name('api.orders.index');

        // POST /api/pedidos → Create new order
        Route::post('/', [OrderController::class, 'store'])
            ->name('api.orders.store');

        // GET /api/pedidos/{id} → View specific order
        Route::get('{id}', [OrderController::class, 'show'])
            ->whereNumber('id')
            ->name('api.orders.show');
    });
});