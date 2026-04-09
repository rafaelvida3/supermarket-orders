<?php

namespace Tests\Feature\Orders;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderControllerDuplicateProductTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_it_merges_duplicate_products_in_the_same_order(): void
    {
        $product = Product::query()->create([
            'name' => 'Rice',
            'price' => 10.50,
            'qty_stock' => 10,
        ]);

        $payload = [
            'customer_name' => 'Rafael',
            'delivery_date' => Carbon::tomorrow()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'qty' => 2],
                ['product_id' => $product->id, 'qty' => 3],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertCreated()
            ->assertJsonFragment([
                'message' => 'Pedido criado com sucesso.',
                'total' => '52.50',
            ]);

        $order = Order::query()->firstOrFail();

        $this->assertDatabaseCount('order_items', 1);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 5,
            'unit_price' => '10.50',
            'subtotal' => '52.50',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'qty_stock' => 5,
        ]);
    }
}
