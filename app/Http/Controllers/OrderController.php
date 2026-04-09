<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CreateOrderAction;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::query()
            ->select(['id', 'customer_name', 'delivery_date', 'total', 'created_at'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $orders,
        ]);
    }

    public function store(StoreOrderRequest $request, CreateOrderAction $create_order_action): JsonResponse
    {
        $order_data = $create_order_action->execute($request->validated());

        return response()->json([
            'data' => [
                'id' => $order_data['order_id'],
                'total' => $order_data['total'],
            ],
            'message' => 'Pedido criado com sucesso.',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::query()
            ->select(['id', 'customer_name', 'delivery_date', 'total', 'created_at'])
            ->with([
                'items' => function ($query) {
                    $query->select(['id', 'order_id', 'product_id', 'qty', 'unit_price', 'subtotal'])
                        ->with('product:id,name');
                },
            ])
            ->find($id);

        if (! $order) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        return response()->json([
            'data' => $order,
        ]);
    }
}
