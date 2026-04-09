<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->select(["id", "name", "price", "qty_stock"]);

        $search = $request->query("q");

        if ($search && strlen($search) >= 2) {
            $query->where("name", "ILIKE", "%{$search}%");
        }

        $products = $query
            ->orderBy("name")
            ->limit(10)
            ->get();

        return response()->json([
            "data" => $products,
        ]);
    }

    public function stock_index(): JsonResponse
    {
        $products = Product::query()
            ->select(["id", "name", "price", "qty_stock"])
            ->orderBy("name")
            ->get();

        return response()->json([
            "data" => $products,
        ]);
    }
}
