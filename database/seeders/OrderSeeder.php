<?php

namespace Database\Seeders;

use App\Actions\Orders\CreateOrderAction;
use Illuminate\Database\Seeder;
use App\Models\Order;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::query()->exists()) {
            $this->command?->info('Orders already exist. Skipping OrderSeeder.');

            return;
        }
        
        /** @var CreateOrderAction $create_order_action */
        $create_order_action = app(CreateOrderAction::class);

        foreach ($this->get_order_payloads() as $order_payload) {
            $create_order_action->execute($order_payload);
        }
    }

    /**
     * @return array<int, array{
     *     customer_name: string,
     *     delivery_date: string,
     *     items: array<int, array{product_id:int, qty:int}>
     * }>
     */
    private function get_order_payloads(): array
    {
        return [
            [
                'customer_name' => 'João Silva',
                'delivery_date' => '2026-04-10',
                'items' => [
                    ['product_id' => 49, 'qty' => 12],
                    ['product_id' => 39, 'qty' => 2],
                    ['product_id' => 15, 'qty' => 1],
                    ['product_id' => 14, 'qty' => 2],
                    ['product_id' => 24, 'qty' => 1],
                ],
            ],
            [
                'customer_name' => 'Marina Souza',
                'delivery_date' => '2026-04-11',
                'items' => [
                    ['product_id' => 19, 'qty' => 6],
                    ['product_id' => 20, 'qty' => 3],
                    ['product_id' => 21, 'qty' => 3],
                    ['product_id' => 22, 'qty' => 1],
                    ['product_id' => 11, 'qty' => 4],
                    ['product_id' => 39, 'qty' => 8],
                    ['product_id' => 40, 'qty' => 3],
                    ['product_id' => 7, 'qty' => 2],
                ],
            ],
            [
                'customer_name' => 'Empresa Atlas',
                'delivery_date' => '2026-04-12',
                'items' => [
                    ['product_id' => 23, 'qty' => 5],
                    ['product_id' => 25, 'qty' => 8],
                    ['product_id' => 24, 'qty' => 4],
                    ['product_id' => 9, 'qty' => 6],
                    ['product_id' => 8, 'qty' => 6],
                    ['product_id' => 12, 'qty' => 10],
                ],
            ],
            [
                'customer_name' => 'Luciana Ferreira',
                'delivery_date' => '2026-04-13',
                'items' => [
                    ['product_id' => 30, 'qty' => 4],
                    ['product_id' => 31, 'qty' => 3],
                    ['product_id' => 32, 'qty' => 3],
                    ['product_id' => 45, 'qty' => 6],
                    ['product_id' => 46, 'qty' => 4],
                    ['product_id' => 5, 'qty' => 6],
                    ['product_id' => 35, 'qty' => 2],
                ],
            ],
            [
                'customer_name' => 'Carlos Lima',
                'delivery_date' => '2026-04-14',
                'items' => [
                    ['product_id' => 1, 'qty' => 2],
                    ['product_id' => 10, 'qty' => 12],
                    ['product_id' => 17, 'qty' => 2],
                    ['product_id' => 13, 'qty' => 4],
                ],
            ],
            [
                'customer_name' => 'Buffet Aurora',
                'delivery_date' => '2026-04-15',
                'items' => [
                    ['product_id' => 22, 'qty' => 2],
                    ['product_id' => 11, 'qty' => 6],
                    ['product_id' => 19, 'qty' => 10],
                    ['product_id' => 20, 'qty' => 6],
                    ['product_id' => 21, 'qty' => 6],
                    ['product_id' => 47, 'qty' => 12],
                    ['product_id' => 40, 'qty' => 5],
                ],
            ],
        ];
    }
}