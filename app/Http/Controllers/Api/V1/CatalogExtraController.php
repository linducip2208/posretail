<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Expense;
use App\Models\Shift;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CatalogExtraController extends Controller
{
    use ShopApi;

    public function brands(Request $request): JsonResponse
    {
        $request->validate(['search' => 'nullable|string|max:100', 'per_page' => 'nullable|integer|min:1']);
        $query = Brand::query()->latest();
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function units(Request $request): JsonResponse
    {
        return response()->json(['data' => Unit::orderBy('name')->get()]);
    }

    public function showBrand(Brand $brand): JsonResponse
    {
        return response()->json(['data' => $brand]);
    }

    public function storeBrand(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'create')) {
            return $deny;
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        $data['slug'] = $this->uniqueBrandSlug($data['slug'] ?? null, $data['name']);

        return response()->json(['data' => Brand::create($data + ['active' => $data['active'] ?? true])], 201);
    }

    public function updateBrand(Request $request, Brand $brand): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'edit')) {
            return $deny;
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brand->id)],
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueBrandSlug($data['slug'] ?? null, $data['name'] ?? $brand->name, $brand->id);
        }

        $brand->update($data);

        return response()->json(['data' => $brand->fresh()]);
    }

    public function showUnit(Unit $unit): JsonResponse
    {
        return response()->json(['data' => $unit]);
    }

    public function storeUnit(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'create')) {
            return $deny;
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:units,code',
        ]);

        return response()->json(['data' => Unit::create($data)], 201);
    }

    public function updateUnit(Request $request, Unit $unit): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'edit')) {
            return $deny;
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('units', 'code')->ignore($unit->id)],
        ]);

        $unit->update($data);

        return response()->json(['data' => $unit->fresh()]);
    }

    protected function uniqueBrandSlug(?string $wanted, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($wanted ?: $name) ?: 'brand';
        $slug = $base;
        $i = 2;

        while (Brand::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function expenses(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'finance')) {
            return $deny;
        }
        $request->validate([
            'outlet_id' => 'nullable|integer|exists:outlets,id',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = Expense::with(['outlet', 'user'])
            ->whereIn('outlet_id', $this->outletIds($request))
            ->latest();
        if ($request->filled('outlet_id')) {
            $oid = (int) $request->outlet_id;
            if (! in_array($oid, $this->outletIds($request), true)) {
                return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
            }
            $query->where('outlet_id', $oid);
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function storeExpense(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'finance', 'create')) {
            return $deny;
        }
        $data = $request->validate([
            'outlet_id' => 'required|integer|exists:outlets,id',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1|max:1000000000',
            'description' => 'nullable|string|max:1000',
            'expense_date' => 'nullable|date',
        ]);

        if (! in_array((int) $data['outlet_id'], $this->outletIds($request), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $expense = Expense::create([
            'outlet_id' => $data['outlet_id'],
            'user_id' => $request->user()->id,
            'category' => $data['category'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'expense_date' => $data['expense_date'] ?? now()->toDateString(),
        ]);

        return response()->json(['data' => $expense], 201);
    }

    public function shifts(Request $request): JsonResponse
    {
        $request->validate(['per_page' => 'nullable|integer|min:1']);

        $query = Shift::with('outlet')
            ->where('user_id', $request->user()->id)
            ->whereIn('outlet_id', $this->outletIds($request))
            ->latest();

        return $this->envelope($query->paginate($this->perPage($request)));
    }
}
