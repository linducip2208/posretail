<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Customer::with('customerGroup')->where('active', true);

        if (! $user->hasPermission('*')) {
            $query->whereHas('orders', fn ($q) => $q->whereIn('outlet_id', $user->getAccessibleOutletIds()));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    public function show(Customer $customer): JsonResponse
    {
        $user = request()->user();
        if (! $user->hasPermission('*') && ! $customer->orders()->whereIn('outlet_id', $user->getAccessibleOutletIds())->exists()) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke customer ini.'], 403);
        }

        $customer->load('customerGroup');

        return response()->json(['data' => $customer]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
        ]);

        $validated['active'] = true;

        $customer = Customer::create($validated);

        return response()->json(['data' => $customer], 201);
    }
}
