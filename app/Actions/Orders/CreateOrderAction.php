<?php

namespace App\Actions\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrderAction
{
    /**
     * Create a new order and debit stock.
     *
     * @param array<string, mixed> $validated_data
     * @return array{order_id:int, total:string}
     *
     * @throws ValidationException
     */
    public function execute(array $validated_data): array
    {
        $aggregated_items = $this->aggregate_items($validated_data['items']);

        return DB::transaction(function () use ($validated_data, $aggregated_items): array {
            $product_ids = collect($aggregated_items)->pluck('product_id')->all();

            $products = Product::query()
                ->whereIn('id', $product_ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $this->validate_products_stock($aggregated_items, $products);

            $order = Order::create([
                'customer_name' => $validated_data['customer_name'],
                'delivery_date' => Carbon::parse($validated_data['delivery_date'])->toDateString(),
                'total' => '0.00',
            ]);

            $total = $this->create_order_items_and_debit_stock($order->id, $aggregated_items, $products);

            $order->update([
                'total' => $total,
            ]);

            return [
                'order_id' => $order->id,
                'total' => $total,
            ];
        });
    }

    /**
     * Consolidate duplicated products into a single order item.
     *
     * @param array<int, array<string, int|string>> $items
     * @return array<int, array{product_id:int, qty:int}>
     */
    private function aggregate_items(array $items): array
    {
        $aggregated_items = [];

        foreach ($items as $item) {
            $product_id = (int) $item['product_id'];
            $qty = (int) $item['qty'];

            if (!isset($aggregated_items[$product_id])) {
                $aggregated_items[$product_id] = [
                    'product_id' => $product_id,
                    'qty' => 0,
                ];
            }

            $aggregated_items[$product_id]['qty'] += $qty;
        }

        return array_values($aggregated_items);
    }

    /**
     * Validate product existence and stock availability.
     *
     * @param array<int, array{product_id:int, qty:int}> $aggregated_items
     * @param \Illuminate\Support\Collection<int, Product> $products
     *
     * @throws ValidationException
     */
    private function validate_products_stock(array $aggregated_items, $products): void
    {
        foreach ($aggregated_items as $item) {
            $product = $products->get($item['product_id']);

            if (!$product) {
                throw ValidationException::withMessages([
                    'items' => ['One or more selected products no longer exist.'],
                ]);
            }

            if ($product->qty_stock < $item['qty']) {
                throw ValidationException::withMessages([
                    'items' => ["Produto {$product->name}: estoque insuficiente ({$product->qty_stock} disponível)."],
                ]);
            }
        }
    }

    /**
     * Create order items, update stock, and calculate total.
     *
     * @param int $order_id
     * @param array<int, array{product_id:int, qty:int}> $aggregated_items
     * @param \Illuminate\Support\Collection<int, Product> $products
     * @return string
     */
    private function create_order_items_and_debit_stock(int $order_id, array $aggregated_items, $products): string
    {
        $total = '0.00';

        foreach ($aggregated_items as $item) {
            $product = $products->get($item['product_id']);
            $subtotal = bcmul((string) $product->price, (string) $item['qty'], 2);
            $total = bcadd($total, $subtotal, 2);

            OrderItem::create([
                'order_id' => $order_id,
                'product_id' => $product->id,
                'qty' => $item['qty'],
                'unit_price' => $product->price,
                'subtotal' => $subtotal,
            ]);

            $product->decrement('qty_stock', $item['qty']);
        }

        return $total;
    }
}