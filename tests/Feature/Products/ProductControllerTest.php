<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductControllerTest extends TestCase {
    use RefreshDatabase;

    #[Test]
    public function test_it_returns_the_products_list_successfully(): void {
        Product::query()->create([
            "name" => "Rice",
            "price" => 10.50,
            "qty_stock" => 20,
        ]);

        $response = $this->getJson("/api/products");

        $response->assertOk()
            ->assertJsonStructure([
                "data" => [
                    "*" => ["id", "name", "price", "qty_stock"],
                ],
            ]);
    }

    #[Test]
    public function test_it_filters_products_by_search_term(): void {
        Product::query()->create([
            "name" => "Rice",
            "price" => 10.50,
            "qty_stock" => 20,
        ]);

        Product::query()->create([
            "name" => "Beans",
            "price" => 8.90,
            "qty_stock" => 15,
        ]);

        $response = $this->getJson("/api/products?q=ri");

        $response->assertOk()
            ->assertJsonCount(1, "data")
            ->assertJsonFragment([
                "name" => "Rice",
            ])
            ->assertJsonMissing([
                "name" => "Beans",
            ]);
    }

    #[Test]
    public function test_it_limits_the_products_list_to_ten_items(): void {
        foreach (range(1, 12) as $index) {
            Product::query()->create([
                "name" => sprintf("Product %02d", $index),
                "price" => 1.99,
                "qty_stock" => 10,
            ]);
        }

        $response = $this->getJson("/api/products");

        $response->assertOk()
            ->assertJsonCount(10, "data");
    }

    #[Test]
    public function test_it_returns_the_full_stock_snapshot(): void {
        Product::query()->create([
            "name" => "Rice",
            "price" => 10.50,
            "qty_stock" => 2,
        ]);

        Product::query()->create([
            "name" => "Beans",
            "price" => 8.90,
            "qty_stock" => 0,
        ]);

        $response = $this->getJson("/api/products/stock");

        $response->assertOk()
            ->assertJsonCount(2, "data")
            ->assertJsonFragment([
                "name" => "Rice",
                "qty_stock" => 2,
            ])
            ->assertJsonFragment([
                "name" => "Beans",
                "qty_stock" => 0,
            ]);
    }
}
