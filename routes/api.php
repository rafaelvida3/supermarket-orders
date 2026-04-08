<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

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

Route::middleware("api")->group(function () {

    /* ===== Product Routes ===== */
    Route::prefix("products")->group(function () {
        // GET /api/products → List or search products
        Route::get("/", [ProductController::class, "index"])
            ->name("api.products.index");

        // GET /api/products/stock → Full inventory snapshot
        Route::get("/stock", [ProductController::class, "stock_index"])
            ->name("api.products.stock");
    });

    /* ===== Order Routes ===== */
    Route::prefix("orders")->group(function () {
        // GET /api/orders → List all orders
        Route::get("/", [OrderController::class, "index"])
            ->name("api.orders.index");

        // POST /api/orders → Create new order
        Route::post("/", [OrderController::class, "store"])
            ->name("api.orders.store");

        // GET /api/orders/{id} → View specific order
        Route::get("{id}", [OrderController::class, "show"])
            ->whereNumber("id")
            ->name("api.orders.show");
    });
});