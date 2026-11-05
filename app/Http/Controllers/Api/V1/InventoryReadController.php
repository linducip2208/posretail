<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryReadController extends Controller
{
    use ShopApi;

    public function movements(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori')) {
            return $deny;
        }
        $request->validate([
            'product_id' => 'nullable|integer|exists:products,id',
            'type' => 'nullable|in:in,out',
            'outlet_id' => 'nullable|integer|exists:outlets,id',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = StockMovement::with(['product', 'outlet'])
            ->whereIn('outlet_id', $this->outletIds($request))
            ->latest();
        foreach (['product_id', 'type', 'outlet_id'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->input($f));
            }
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function rawMaterials(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori')) {
            return $deny;
        }
        $request->validate([
            'search' => 'nullable|string|max:100',
            'low_stock' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = RawMaterial::with(['outlet', 'unit'])
            ->whereIn('outlet_id', $this->outletIds($request))
            ->latest();
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->boolean('low_stock')) {
            $query->whereColumn('current_stock', '<=', 'min_stock');
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function storeRawMaterial(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'create')) {
            return $deny;
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:raw_materials,code',
            'outlet_id' => 'required|integer|exists:outlets,id',
            'unit_id' => 'required|integer|exists:units,id',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
        ]);

        if (! in_array((int) $data['outlet_id'], $this->outletIds($request), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        return response()->json(['data' => RawMaterial::create($data + ['active' => true])], 201);
    }

    public function updateRawMaterial(Request $request, RawMaterial $rawMaterial): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'edit')) {
            return $deny;
        }
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => ['nullable', 'string', 'max:50', Rule::unique('raw_materials', 'code')->ignore($rawMaterial->id)],
            'outlet_id' => 'nullable|integer|exists:outlets,id',
            'unit_id' => 'nullable|integer|exists:units,id',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);

        if (array_key_exists('outlet_id', $data) && filled($data['outlet_id'] ?? null)
            && ! in_array((int) $data['outlet_id'], $this->outletIds($request), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        if (! in_array((int) $rawMaterial->outlet_id, $this->outletIds($request), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data ini.'], 403);
        }

        $rawMaterial->update($data);

        return response()->json(['data' => $rawMaterial->fresh()->load(['outlet', 'unit'])]);
    }
}
