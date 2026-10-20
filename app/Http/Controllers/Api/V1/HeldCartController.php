<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\HeldCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeldCartController extends Controller
{
    use ShopApi;

    public function index(Request $request): JsonResponse
    {
        $request->validate(['per_page' => 'nullable|integer|min:1']);

        $query = HeldCart::with(['outlet', 'customer'])
            ->whereIn('outlet_id', $this->outletIds($request))
            ->where('user_id', $request->user()->id)
            ->latest();

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'outlet_id' => 'required|integer|exists:outlets,id',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'label' => 'nullable|string|max:100',
            'items' => 'required|array|min:1|max:200',
        ]);

        if (! in_array((int) $data['outlet_id'], $this->outletIds($request), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $cart = HeldCart::create([
            'outlet_id' => $data['outlet_id'],
            'user_id' => $request->user()->id,
            'customer_id' => $data['customer_id'] ?? null,
            'label' => $data['label'] ?? null,
            'items' => $data['items'],
        ]);

        return response()->json(['data' => $cart], 201);
    }

    public function resume(Request $request, HeldCart $heldCart): JsonResponse
    {
        if ((int) $heldCart->user_id !== (int) $request->user()->id
            && ! $request->user()->hasPermission('*')) {
            return response()->json(['message' => 'Bukan keranjang Anda.'], 403);
        }
        if (! in_array((int) $heldCart->outlet_id, $this->outletIds($request), true)) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }

        return response()->json(['data' => $heldCart]);
    }

    public function destroy(Request $request, HeldCart $heldCart): JsonResponse
    {
        if ((int) $heldCart->user_id !== (int) $request->user()->id
            && ! $request->user()->hasPermission('*')) {
            return response()->json(['message' => 'Bukan keranjang Anda.'], 403);
        }
        $heldCart->delete();

        return response()->json(['message' => 'Keranjang dihapus.']);
    }
}
