<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\MarketplaceOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FulfillmentController extends Controller
{
    use ShopApi;

    public function deliveries(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'operasional')) {
            return $deny;
        }
        $request->validate([
            'status' => 'nullable|string|max:30',
            'outlet_id' => 'nullable|integer|exists:outlets,id',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = Delivery::with(['order', 'outlet'])
            ->whereIn('outlet_id', $this->outletIds($request))
            ->latest();
        foreach (['status', 'outlet_id'] as $f) {
            if ($request->filled($f)) {
                if ($f === 'outlet_id' && ! in_array((int) $request->outlet_id, $this->outletIds($request), true)) {
                    return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
                }
                $query->where($f, $request->input($f));
            }
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function advanceDelivery(Request $request, Delivery $delivery): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'operasional', 'edit')) {
            return $deny;
        }
        if (! in_array((int) $delivery->outlet_id, $this->outletIds($request), true)) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }

        // Alur web: pending → packed → shipped → delivered
        $next = match ($delivery->status) {
            'pending' => ['status' => 'packed', 'packed_at' => now()],
            'packed' => ['status' => 'shipped', 'shipped_at' => now()],
            'shipped' => ['status' => 'delivered', 'delivered_at' => now()],
            default => null,
        };
        if ($next === null) {
            return response()->json(['data' => $delivery, 'message' => 'Status sudah final.']);
        }

        $delivery->update($next);

        return response()->json(['data' => $delivery->fresh()]);
    }

    public function marketplaceOrders(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'transaksi')) {
            return $deny;
        }
        $request->validate([
            'platform' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:30',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = MarketplaceOrder::latest();
        foreach (['platform', 'status'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->input($f));
            }
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }
}
