<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = Product::with(['category', 'unit', 'variants'])
            ->where('active', true);

        if ($request->filled('search')) {
            $search = substr($request->search, 0, 100);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', $search);
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(min(100, max(1, (int) ($request->per_page ?? 50))));

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'unit', 'variants']);

        return response()->json(['data' => $product]);
    }

    public function byBarcode(Request $request): JsonResponse
    {
        $request->validate(['barcode' => 'required|string|max:100']);

        $product = Product::with(['category', 'unit', 'variants'])
            ->where('barcode', $request->barcode)
            ->orWhereHas('variants', fn ($q) => $q->where('barcode', $request->barcode))
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json(['data' => $product]);
    }
}
