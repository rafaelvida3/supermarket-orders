<?php

namespace Tests\Unit\Orders;

use App\Actions\Orders\CreateOrderAction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateOrderActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_it_aggregates_duplicated_products_before_creating_order_items(): void
    {
        Product::query()->create([
            'id' => 1,
            'name' => 'Rice',
            'price' => 10.00,
            'qty_stock' => 10,
        ]);

        $create_order_action = new CreateOrderAction();

        $result = $create_order_action->execute([
            'customer_name' => 'Rafael',
            'delivery_date' => now()->addDay()->toDateString(),
            'items' => [
                [
                    'product_id' => 1,
                    'qty' => 2,
                ],
                [
                    'product_id' => 1,
                    'qty' => 3,
                ],
            ],
        ]);

        $this->assertSame('50.00', $result['total']);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);

        $order = Order::query()->findOrFail($result['order_id']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_name' => 'Rafael',
            'total' => '50.00',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => 1,
            'qty' => 5,
            'unit_price' => '10.00',
            'subtotal' => '50.00',
        ]);

        $this->assertSame(5, Product::query()->findOrFail(1)->qty_stock);
    }

    #[Test]
    public function test_it_throws_validation_exception_when_stock_is_insufficient(): void
    {
        Product::query()->create([
            'id' => 1,
            'name' => 'Rice',
            'price' => 10.00,
            'qty_stock' => 3,
        ]);

        $create_order_action = new CreateOrderAction();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('estoque insuficiente');

        $create_order_action->execute([
            'customer_name' => 'Rafael',
            'delivery_date' => now()->addDay()->toDateString(),
            'items' => [
                [
                    'product_id' => 1,
                    'qty' => 4,
                ],
            ],
        ]);
    }
}