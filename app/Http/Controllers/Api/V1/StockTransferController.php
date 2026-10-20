<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    use ShopApi;

    public function index(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori')) {
            return $deny;
        }
        $request->validate([
            'status' => 'nullable|string|max:30',
            'outlet_id' => 'nullable|integer|exists:outlets,id',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $ids = $this->outletIds($request);
        $query = StockTransfer::with(['fromOutlet', 'toOutlet', 'user'])
            ->where(fn ($q) => $q->whereIn('from_outlet_id', $ids)->orWhereIn('to_outlet_id', $ids))
            ->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('outlet_id')) {
            $oid = (int) $request->outlet_id;
            if (! in_array($oid, $ids, true)) {
                return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
            }
            $query->where(fn ($q) => $q->where('from_outlet_id', $oid)->orWhere('to_outlet_id', $oid));
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function store(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'create')) {
            return $deny;
        }
        $data = $request->validate([
            'from_outlet_id' => 'required|integer|exists:outlets,id|different:to_outlet_id',
            'to_outlet_id' => 'required|integer|exists:outlets,id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1|max:200',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:100000',
        ]);

        $ids = $this->outletIds($request);
        foreach ([(int) $data['from_outlet_id'], (int) $data['to_outlet_id']] as $oid) {
            if (! in_array($oid, $ids, true)) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
            }
        }

        $transfer = DB::transaction(function () use ($data, $request) {
            $prefix = 'STF-' . now()->format('Ymd') . '-';
            $last = StockTransfer::where('transfer_number', 'like', $prefix . '%')
                ->orderByDesc('transfer_number')->first();
            $num = $last ? ((int) substr($last->transfer_number, -4) + 1) : 1;

            $transfer = StockTransfer::create([
                'transfer_number' => $prefix . str_pad($num, 4, '0', STR_PAD_LEFT),
                'from_outlet_id' => $data['from_outlet_id'],
                'to_outlet_id' => $data['to_outlet_id'],
                'user_id' => $request->user()->id,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);
            foreach ($data['items'] as $i) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $i['product_id'],
                    'quantity' => $i['quantity'],
                ]);
            }

            return $transfer;
        });

        return response()->json(['data' => $transfer->load('stockTransferItems')], 201);
    }

    public function send(Request $request, StockTransfer $stockTransfer): JsonResponse
    {
        return $this->transition($request, $stockTransfer, 'sent', 'draft');
    }

    /**
     * Terima transfer — sama seperti web (catat status + jejak mutasi
     * audit per outlet; stok global mengikuti alur web).
     */
    public function receive(Request $request, StockTransfer $stockTransfer): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'edit')) {
            return $deny;
        }
        $ids = $this->outletIds($request);
        if (! in_array((int) $stockTransfer->from_outlet_id, $ids, true)
            && ! in_array((int) $stockTransfer->to_outlet_id, $ids, true)) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }
        if ($stockTransfer->status === 'received') {
            return response()->json(['data' => $stockTransfer, 'message' => 'Transfer sudah diterima sebelumnya.']);
        }

        DB::transaction(function () use ($stockTransfer) {
            foreach ($stockTransfer->stockTransferItems as $item) {
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'outlet_id' => $stockTransfer->from_outlet_id,
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'reference_type' => 'transfer',
                    'reference_id' => $stockTransfer->id,
                    'notes' => 'Transfer keluar #' . $stockTransfer->transfer_number,
                ]);
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'outlet_id' => $stockTransfer->to_outlet_id,
                    'type' => 'in',
                    'quantity' => $item->quantity,
                    'reference_type' => 'transfer',
                    'reference_id' => $stockTransfer->id,
                    'notes' => 'Transfer masuk #' . $stockTransfer->transfer_number,
                ]);
            }
            $stockTransfer->update(['status' => 'received']);
        });

        return response()->json(['data' => $stockTransfer->fresh()->load('stockTransferItems')]);
    }

    private function transition(Request $request, StockTransfer $stockTransfer, string $to, string $from): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'edit')) {
            return $deny;
        }
        $ids = $this->outletIds($request);
        if (! in_array((int) $stockTransfer->from_outlet_id, $ids, true)) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }
        if ($stockTransfer->status !== $from) {
            return response()->json(['message' => "Status harus {$from} untuk aksi ini."], 422);
        }
        $stockTransfer->update(['status' => $to]);

        return response()->json(['data' => $stockTransfer->fresh()]);
    }
}
