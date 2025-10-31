<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

/**
 * Controller responsible for handling product-related requests.
 * Provides endpoints for fetching product data with optional search filtering.
 */
class ProductController extends Controller
{
    /**
     * Retrieve a list of products for autocomplete or general listing.
     *
     * Supports an optional "q" query parameter for name-based search.
     * Limits results to 10 items to avoid overloading the frontend autocomplete.
     *
     * @param Request $request The incoming HTTP request containing optional search term.
     * @return JsonResponse JSON response with the list of matching products.
     */
    public function index(Request $request): JsonResponse {
        // Base query selecting only the required product fields
        $query = Product::select([
            'id',
            'name',
            'price',
            'qty_stock'
        ]);

        // If a search term is provided, filter products by name (case-insensitive LIKE)
        if ($q = $request->query('q')) {
            $query->where('name', 'LIKE', "%{$q}%");
        }

        // Limit results to improve performance and avoid overloading the autocomplete
        $products = $query->orderBy('name')->limit(10)->get();

        // Return the product list as JSON
        return response()->json($products);
    }
}