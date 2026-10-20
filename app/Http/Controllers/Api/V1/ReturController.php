<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\Retur;
use App\Models\ReturnItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    use ShopApi;

    public function index(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'transaksi')) {
            return $deny;
        }
        $request->validate([
            'status' => 'nullable|string|max:30',
            'outlet_id' => 'nullable|integer|exists:outlets,id',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = Retur::with(['order', 'outlet', 'user'])
            ->whereIn('outlet_id', $this->outletIds($request))
            ->latest();
        foreach (['status', 'outlet_id'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->input($f));
            }
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function store(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'transaksi', 'create')) {
            return $deny;
        }
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1|max:200',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:100000',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        $order = \App\Models\Order::findOrFail($data['order_id']);
        if (! in_array((int) $order->outlet_id, $this->outletIds($request), true)) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }

        try {
            Retur::assertCumulativeQty($order, collect($data['items'])->map(fn ($i) => [
                'product_id' => (int) $i['product_id'],
                'quantity' => (int) $i['quantity'],
            ])->all());
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }

        $retur = DB::transaction(function () use ($data, $order, $request) {
            $total = 0;
            foreach ($data['items'] as $i) {
                $price = $i['unit_price'] ?? (float) (\App\Models\OrderItem::where('order_id', $order->id)
                    ->where('product_id', $i['product_id'])->value('unit_price') ?? 0);
                $total += $price * $i['quantity'];
            }
            $retur = Retur::create([
                'order_id' => $order->id,
                'outlet_id' => $order->outlet_id,
                'user_id' => $request->user()->id,
                'type' => 'customer_return',
                'total_amount' => $total,
                'reason' => $data['reason'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);
            foreach ($data['items'] as $i) {
                $price = $i['unit_price'] ?? (float) (\App\Models\OrderItem::where('order_id', $order->id)
                    ->where('product_id', $i['product_id'])->value('unit_price') ?? 0);
                ReturnItem::create([
                    'return_id' => $retur->id,
                    'product_id' => $i['product_id'],
                    'quantity' => $i['quantity'],
                    'unit_price' => $price,
                    'subtotal' => $price * $i['quantity'],
                ]);
            }

            return $retur;
        });

        return response()->json(['data' => $retur->load('returnItems')], 201);
    }

    /**
     * Setujui retur — set status completed; event model otomatis
     * mengembalikan stok + movement + refund pembayaran (idempoten).
     */
    public function approve(Request $request, Retur $retur): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'transaksi', 'edit')) {
            return $deny;
        }
        if (! in_array((int) $retur->outlet_id, $this->outletIds($request), true)) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }
        if ($retur->status === 'completed') {
            return response()->json(['data' => $retur, 'message' => 'Retur sudah selesai sebelumnya.']);
        }

        $retur->update(['status' => 'completed']);

        return response()->json(['data' => $retur->fresh()->load('returnItems')]);
    }
}
