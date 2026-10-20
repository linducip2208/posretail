<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ShopApi;

    public function index(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'pembelian')) {
            return $deny;
        }
        $request->validate([
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = Supplier::query()->latest();
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function store(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'master-data', 'create')) {
            return $deny;
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
        ]);

        $supplier = Supplier::create($data + ['active' => true]);

        return response()->json(['data' => $supplier], 201);
    }

    public function show(Request $request, Supplier $supplier): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'pembelian')) {
            return $deny;
        }
        $supplier->loadCount(['purchaseOrders as po_count']);

        return response()->json(['data' => $supplier]);
    }
}
