<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CreateOrderAction;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

/**
 * Controller responsible for managing orders.
 */
class OrderController extends Controller
{
    /**
     * Retrieve a list of all orders.
     *
     * @return Collection List of orders with basic information.
     */
    public function index(): Collection
    {
        return Order::select('id', 'customer_name', 'delivery_date', 'total', 'created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Create a new order.
     *
     * @param StoreOrderRequest $request Validated order request.
     * @param CreateOrderAction $create_order_action Action responsible for order creation.
     * @return JsonResponse JSON response with created order metadata.
     */
    public function store(StoreOrderRequest $request, CreateOrderAction $create_order_action): JsonResponse
    {
        $order_data = $create_order_action->execute($request->validated());

        return response()->json([
            'order_id' => $order_data['order_id'],
            'total' => $order_data['total'],
            'message' => 'Pedido criado com sucesso.',
        ], 201);
    }

    /**
     * Retrieve a single order with its items and products.
     *
     * @param int $id Order ID.
     * @return JsonResponse JSON response with order details or 404 if not found.
     */
    public function show(int $id): JsonResponse
    {
        $order = Order::select([
            'id',
            'customer_name',
            'delivery_date',
            'total',
            'created_at',
        ])->with([
            'items' => function ($query) {
                $query->select('id', 'order_id', 'product_id', 'qty', 'unit_price', 'subtotal')
                    ->with('product:id,name');
            },
        ])->find($id);

        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado'], 404);
        }

        return response()->json($order);
    }
}