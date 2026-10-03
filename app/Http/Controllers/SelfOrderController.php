<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Product;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * QR self-order publik (tanpa login): tamu scan QR meja → pesan → masuk KDS.
 * Throttle ketat + harga server + stok lock via CheckoutService.
 */
class SelfOrderController extends Controller
{
    public function store(Request $request, Outlet $outlet, CheckoutService $checkout): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1|max:50',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:20',
            'table_id' => 'nullable|exists:tables,id',
            'customer_name' => 'nullable|string|max:100',
            'order_notes' => 'nullable|string|max:500',
        ]);

        if (! $outlet->active) {
            return response()->json(['message' => 'Outlet tidak aktif.'], 422);
        }

        // Hanya produk aktif milik outlet ini / global
        $items = collect($request->items)->map(fn ($i) => [
            'product_id' => (int) $i['product_id'],
            'product_variant_id' => $i['product_variant_id'] ?? null,
            'quantity' => (int) $i['quantity'],
            'discount_percent' => 0,
        ])->toArray();

        foreach ($items as $it) {
            $p = Product::find($it['product_id']);
            if (! $p || ! $p->active || ($p->outlet_id && (int) $p->outlet_id !== (int) $outlet->id)) {
                return response()->json(['message' => 'Ada item tidak tersedia di outlet ini.'], 422);
            }
        }

        // user_id sistem: pakai user pertama outlet / fallback 1 (catat sebagai self-order)
        $cashierId = $outlet->users()->first()?->id ?? 1;

        $order = $checkout->checkout([
            'outlet_id' => $outlet->id,
            'items' => $items,
            'payments' => [], // pending, bayar di kasir
            'table_id' => $request->table_id,
            'order_type' => 'self_order',
            'order_status' => 'pending',
            'order_notes' => trim(($request->customer_name ? '['.$request->customer_name.'] ' : '').($request->order_notes ?? '')),
            'notes' => '[qr-self-order]',
            'use_tax' => false,
        ], (int) $cashierId);

        return response()->json([
            'order_number' => $order->order_number,
            'queue_number' => $order->queue_number,
            'total' => $order->total_amount,
            'message' => 'Pesanan diterima, tunjukkan nomor antrian ke kasir untuk pembayaran.',
        ], 201);
    }
}
