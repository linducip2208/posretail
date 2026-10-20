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
