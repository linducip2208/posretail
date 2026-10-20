<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    use ShopApi;

    public function index(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'pembelian')) {
            return $deny;
        }
        $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:30',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'outlet_id' => 'nullable|integer|exists:outlets,id',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = PurchaseOrder::with(['supplier', 'outlet', 'user'])
            ->whereIn('outlet_id', $this->outletIds($request))
            ->latest();

        if ($request->filled('outlet_id') && ! in_array((int) $request->outlet_id, $this->outletIds($request), true)) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }

        if ($request->filled('search')) {
            $query->where('po_number', 'like', "%{$request->search}%");
        }
        foreach (['status', 'supplier_id', 'outlet_id'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->input($f));
            }
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function store(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'pembelian', 'create')) {
            return $deny;
        }
        $data = $request->validate([
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'outlet_id' => 'required|integer|exists:outlets,id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1|max:200',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:100000',
            'items.*.unit_price' => 'required|numeric|min:0|max:1000000000',
        ]);

        if (! in_array((int) $data['outlet_id'], $this->outletIds($request), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $po = DB::transaction(function () use ($data, $request) {
            $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['unit_price']);
            $po = PurchaseOrder::create([
                'po_number' => PurchaseOrder::generateNumber(),
                'supplier_id' => $data['supplier_id'],
                'outlet_id' => $data['outlet_id'],
                'user_id' => $request->user()->id,
                'total_amount' => $total,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);
            foreach ($data['items'] as $i) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $i['product_id'],
                    'quantity' => $i['quantity'],
                    'unit_price' => $i['unit_price'],
                    'subtotal' => $i['quantity'] * $i['unit_price'],
                ]);
            }

            return $po;
        });

        return response()->json(['data' => $po->load(['supplier', 'purchaseOrderItems.product'])], 201);
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'pembelian')) {
            return $deny;
        }
        if (! in_array((int) $purchaseOrder->outlet_id, $this->outletIds($request), true)) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }

        return response()->json(['data' => $purchaseOrder->load(['supplier', 'outlet', 'user', 'purchaseOrderItems.product'])]);
    }

    /**
     * Terima PO — sama persis seperti web: ubah status (event model
     * otomatis memposting jurnal). Stok & payable dicatat manual
     * seperti alur web (tidak ada auto-stok di aplikasi ini).
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'pembelian', 'edit')) {
            return $deny;
        }
        if (! in_array((int) $purchaseOrder->outlet_id, $this->outletIds($request), true)) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }
        if ($purchaseOrder->status === 'received') {
            return response()->json(['data' => $purchaseOrder, 'message' => 'PO sudah diterima sebelumnya.']);
        }

        $purchaseOrder->update(['status' => 'received']);

        return response()->json(['data' => $purchaseOrder->fresh()]);
    }
}
