<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration that creates the core database tables for the order system.
 *
 * Tables created:
 *  - products: stores product catalog and stock information
 *  - orders: stores customer orders
 *  - order_items: links products to orders (many-to-one relationship)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the products, orders, and order_items tables
     * with appropriate fields, constraints, and relationships.
     */
    public function up(): void
    {
        /* ===== Products table ===== */
        Schema::create("products", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->decimal("price", 10, 2);
            $table->unsignedInteger("qty_stock");
            $table->timestamps();
        });

        DB::statement("ALTER TABLE products ADD CONSTRAINT products_price_check CHECK (price >= 0)");
        DB::statement("ALTER TABLE products ADD CONSTRAINT products_qty_stock_check CHECK (qty_stock >= 0)");

        /* ===== Orders table ===== */
        Schema::create("orders", function (Blueprint $table) {
            $table->id();
            $table->string("customer_name");
            $table->date("delivery_date");
            $table->decimal("total", 10, 2)->default(0);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_total_check CHECK (total >= 0)");

        /* ===== Order items table ===== */
        Schema::create("order_items", function (Blueprint $table) {
            $table->id();
            $table->foreignId("order_id")
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId("product_id")
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedInteger("qty");
            $table->decimal("unit_price", 10, 2);
            $table->decimal("subtotal", 10, 2);
            $table->timestamps();

            $table->unique(["order_id", "product_id"]);
        });

        DB::statement("ALTER TABLE order_items ADD CONSTRAINT order_items_qty_check CHECK (qty > 0)");
        DB::statement("ALTER TABLE order_items ADD CONSTRAINT order_items_unit_price_check CHECK (unit_price >= 0)");
        DB::statement("ALTER TABLE order_items ADD CONSTRAINT order_items_subtotal_check CHECK (subtotal >= 0)");
    }

    /**
     * Reverse the migrations.
     *
     * Drops all related tables in reverse order to maintain FK integrity.
     */
    public function down(): void
    {
        Schema::dropIfExists("order_items");
        Schema::dropIfExists("orders");
        Schema::dropIfExists("products");
    }
};