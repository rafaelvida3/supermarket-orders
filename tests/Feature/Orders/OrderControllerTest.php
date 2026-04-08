<?php

namespace Tests\Feature\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderControllerTest extends TestCase {
    use RefreshDatabase;

    #[Test]
    public function test_it_creates_an_order_and_decrements_stock(): void {
        $rice = Product::query()->create([
            "name" => "Rice",
            "price" => 10.50,
            "qty_stock" => 10,
        ]);

        $beans = Product::query()->create([
            "name" => "Beans",
            "price" => 7.00,
            "qty_stock" => 8,
        ]);

        $payload = [
            "customer_name" => "Rafael",
            "delivery_date" => Carbon::tomorrow()->toDateString(),
            "items" => [
                ["product_id" => $rice->id, "qty" => 2],
                ["product_id" => $beans->id, "qty" => 1],
            ],
        ];

        $response = $this->postJson("/api/orders", $payload);

        $response->assertCreated()
            ->assertJsonPath("message", "Pedido criado com sucesso.")
            ->assertJsonPath("data.total", "28.00");
            
        $this->assertDatabaseHas("orders", [
            "customer_name" => "Rafael",
            "delivery_date" => Carbon::tomorrow()->toDateString(),
            "total" => "28.00",
        ]);

        $order = Order::query()->firstOrFail();

        $this->assertSame($order->id, $response->json("data.id"));

        $this->assertDatabaseHas("order_items", [
            "order_id" => $order->id,
            "product_id" => $rice->id,
            "qty" => 2,
            "unit_price" => "10.50",
            "subtotal" => "21.00",
        ]);

        $this->assertDatabaseHas("order_items", [
            "order_id" => $order->id,
            "product_id" => $beans->id,
            "qty" => 1,
            "unit_price" => "7.00",
            "subtotal" => "7.00",
        ]);

        $this->assertDatabaseHas("products", [
            "id" => $rice->id,
            "qty_stock" => 8,
        ]);

        $this->assertDatabaseHas("products", [
            "id" => $beans->id,
            "qty_stock" => 7,
        ]);
    }

    #[Test]
    public function test_it_rejects_an_order_when_stock_is_insufficient(): void {
        $product = Product::query()->create([
            "name" => "Rice",
            "price" => 10.50,
            "qty_stock" => 2,
        ]);

        $payload = [
            "customer_name" => "Rafael",
            "delivery_date" => Carbon::tomorrow()->toDateString(),
            "items" => [
                ["product_id" => $product->id, "qty" => 3],
            ],
        ];

        $response = $this->postJson("/api/orders", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(["items"]);

        $this->assertDatabaseCount("orders", 0);
        $this->assertDatabaseCount("order_items", 0);
        $this->assertDatabaseHas("products", [
            "id" => $product->id,
            "qty_stock" => 2,
        ]);
    }

    #[Test]
    public function test_it_returns_the_orders_list_in_descending_order(): void {
        $older_order = Order::query()->create([
            "customer_name" => "Older Customer",
            "delivery_date" => Carbon::tomorrow()->toDateString(),
            "total" => 10.00,
        ]);

        $newer_order = Order::query()->create([
            "customer_name" => "Newer Customer",
            "delivery_date" => Carbon::tomorrow()->toDateString(),
            "total" => 20.00,
        ]);

        $response = $this->getJson("/api/orders");

        $response->assertOk();

        $response_data = $response->json("data");

        $this->assertSame($newer_order->id, $response_data[0]["id"]);
        $this->assertSame($older_order->id, $response_data[1]["id"]);
    }

    #[Test]
    public function test_it_returns_the_order_details(): void {
        $product = Product::query()->create([
            "name" => "Rice",
            "price" => 10.50,
            "qty_stock" => 10,
        ]);

        $order = Order::query()->create([
            "customer_name" => "Rafael",
            "delivery_date" => Carbon::tomorrow()->toDateString(),
            "total" => 21.00,
        ]);

        OrderItem::query()->create([
            "order_id" => $order->id,
            "product_id" => $product->id,
            "qty" => 2,
            "unit_price" => 10.50,
            "subtotal" => 21.00,
        ]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonFragment([
                "id" => $order->id,
                "customer_name" => "Rafael",
                "name" => "Rice",
            ])
            ->assertJsonStructure([
                "data" => [
                    "id",
                    "customer_name",
                    "delivery_date",
                    "total",
                    "created_at",
                    "items" => [
                        "*" => ["id", "order_id", "product_id", "qty", "unit_price", "subtotal", "product"],
                    ],
                ],
            ]);
    }

    #[Test]
    public function test_it_returns_not_found_for_a_missing_order(): void {
        $response = $this->getJson("/api/orders/999999");

        $response->assertNotFound()
            ->assertJsonFragment([
                "message" => "Pedido não encontrado.",
            ]);
    }
}
