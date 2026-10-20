<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
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

        $query = StockOpname::with(['outlet', 'user'])
            ->whereIn('outlet_id', $this->outletIds($request))
            ->latest();
        if ($request->filled('outlet_id') && ! in_array((int) $request->outlet_id, $this->outletIds($request), true)) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }
        foreach (['status', 'outlet_id'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->input($f));
            }
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function store(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'create')) {
            return $deny;
        }
        $data = $request->validate([
            'outlet_id' => 'required|integer|exists:outlets,id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1|max:500',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.actual_stock' => 'required|integer|min:0|max:1000000',
        ]);

        if (! in_array((int) $data['outlet_id'], $this->outletIds($request), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $opname = DB::transaction(function () use ($data, $request) {
            $prefix = 'SO-' . now()->format('Ymd') . '-';
            $last = StockOpname::where('opname_number', 'like', $prefix . '%')
                ->orderByDesc('opname_number')->first();
            $num = $last ? ((int) substr($last->opname_number, -4) + 1) : 1;

            $opname = StockOpname::create([
                'opname_number' => $prefix . str_pad($num, 4, '0', STR_PAD_LEFT),
                'outlet_id' => $data['outlet_id'],
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $i) {
                $system = (int) (Product::where('id', $i['product_id'])->value('current_stock') ?? 0);
                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $i['product_id'],
                    'system_stock' => $system,
                    'actual_stock' => $i['actual_stock'],
                    'difference' => $i['actual_stock'] - $system,
                ]);
            }

            return $opname;
        });

        return response()->json(['data' => $opname->load('stockOpnameItems')], 201);
    }

    /**
     * Setujui opname: terapkan selisih ke stok (actual - system),
     * sama seperti alur stock take web.
     */
    public function approve(Request $request, StockOpname $stockOpname): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'edit')) {
            return $deny;
        }
        if (! in_array((int) $stockOpname->outlet_id, $this->outletIds($request), true)) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }
        if ($stockOpname->status === 'completed') {
            return response()->json(['data' => $stockOpname, 'message' => 'Opname sudah selesai sebelumnya.']);
        }

        DB::transaction(function () use ($stockOpname) {
            foreach ($stockOpname->stockOpnameItems as $item) {
                if ((int) $item->difference !== 0) {
                    Product::where('id', $item->product_id)->update(['current_stock' => $item->actual_stock]);
                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'outlet_id' => $stockOpname->outlet_id,
                        'type' => $item->difference > 0 ? 'in' : 'out',
                        'quantity' => abs($item->difference),
                        'reference_type' => 'opname',
                        'reference_id' => $stockOpname->id,
                        'notes' => 'Koreksi opname #' . $stockOpname->opname_number,
                    ]);
                }
            }
            $stockOpname->update(['status' => 'completed']);
        });

        return response()->json(['data' => $stockOpname->fresh()]);
    }
}
