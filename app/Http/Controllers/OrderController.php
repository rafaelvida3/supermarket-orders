<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * Controller responsible for managing Orders.
 * Handles listing, creation, and detailed retrieval of orders.
 */
class OrderController extends Controller
{

    /**
     * Retrieve a list of all orders.
     *
     * @return Collection List of orders with basic information.
     */
    public function index(): Collection {
        return Order::select('id', 'customer_name', 'delivery_date', 'total', 'created_at')
            ->orderByDesc('id')
            ->get();
    }
    
    /**
     * Create a new order with its associated items.
     *
     * Validates the request, checks product stock,
     * creates the order and related items in a database transaction.
     *
     * @param Request $r The incoming HTTP request.
     * @return JsonResponse JSON response with order details or validation error.
     *
     * @throws ValidationException If product stock is insufficient.
     */
    public function store(Request $r): JsonResponse {
        // Validate input data
        $data = $r->validate([
            'customer_name'        => 'required|string|max:120',
            'delivery_date'        => 'required|date|after_or_equal:today',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer|exists:products,id',
            'items.*.qty'          => 'required|integer|min:1',
        ]);

        // Wrap all operations in a transaction for data integrity
        return DB::transaction(function () use ($data) {

            // Lock selected products for update to avoid concurrent stock modification
            $ids = collect($data['items'])->pluck('product_id')->all();
            $products = Product::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            // Validate stock availability and calculate total
            $total = 0;
            foreach ($data['items'] as $it) {
                $p = $products[$it['product_id']];
                if ($p->qty_stock < $it['qty']) {
                    throw ValidationException::withMessages([
                        'items' => ["Produto {$p->name}: estoque insuficiente ({$p->qty_stock} disponível)."]
                    ]);
                }
            }

            // Create order record (total will be updated later)
            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'delivery_date' => Carbon::parse($data['delivery_date'])->toDateString(),
                'total'         => 0,
            ]);

            // Create order items and update stock
            foreach ($data['items'] as $it) {
                $p = $products[$it['product_id']];
                $subtotal = bcmul((string)$p->price, (string)$it['qty'], 2);
                $total = bcadd((string)$total, (string)$subtotal, 2);

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $p->id,
                    'qty'        => $it['qty'],
                    'unit_price' => $p->price,
                    'subtotal'   => $subtotal,
                ]);

                // Decrease stock quantity
                $p->decrement('qty_stock', $it['qty']);
            }

            // Update order total after all items are processed
            $order->update(['total' => $total]);

            // Return successful response
            return response()->json([
                'order_id' => $order->id,
                'total'    => $total,
                'message'  => 'Pedido criado com sucesso.'
            ], 201);
        });
    }

    /**
     * Retrieve a single order with its items and products.
     *
     * @param int $id Order ID.
     * @return JsonResponse JSON response with order details or 404 if not found.
     */
    public function show(int $id): JsonResponse {
        $order = Order::select([
            'id',
            'customer_name',
            'delivery_date',
            'total',
            'created_at'
        ])->with([
            'items' => function ($query) {
                $query->select('id', 'order_id', 'product_id', 'qty', 'unit_price', 'subtotal')
                    ->with('product:id,name');
            }
        ])->find($id);

        // Handle missing order
        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado'], 404);
        }

        // Return detailed order data
        return response()->json($order);
    }
}
