<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    public function up(): void {
        /* ===== Products table ===== */
        Schema::create('products', function (Blueprint $table) {
            $table->id();                                // Primary key
            $table->string('name');                      // Product name
            $table->decimal('price', 10, 2);             // Product price
            $table->unsignedInteger('qty_stock');        // Available stock quantity
            $table->timestamps();                        // created_at / updated_at
        });

        /* ===== Orders table ===== */
        Schema::create('orders', function (Blueprint $table) {
            $table->id();                                // Primary key
            $table->string('customer_name');             // Customer name
            $table->date('delivery_date');               // Expected delivery date
            $table->decimal('total', 10, 2)->default(0); // Order total amount
            $table->timestamps();                        // created_at / updated_at
        });

        /* ===== Order items table ===== */
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();                                // Primary key
            $table->foreignId('order_id')                // FK → orders.id
                  ->constrained()
                  ->cascadeOnDelete();                   // Deletes items when order is deleted
            $table->foreignId('product_id')              // FK → products.id
                  ->constrained()
                  ->restrictOnDelete();                  // Prevents deleting products in active orders
            $table->unsignedInteger('qty');              // Quantity of product in this order
            $table->decimal('unit_price', 10, 2);        // Unit price at time of order
            $table->decimal('subtotal', 10, 2);          // qty * unit_price
            $table->timestamps();                        // created_at / updated_at

            // Prevents duplicate product entries within the same order
            $table->unique(['order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops all related tables in reverse order to maintain FK integrity.
     */
    public function down(): void {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
    }
};
