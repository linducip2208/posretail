<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use App\Models\GiftCard;
use App\Models\SalesTarget;
use App\Models\SerialNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogLookupController extends Controller
{
    use ShopApi;

    public function serials(Request $request): JsonResponse
    {
        $request->validate([
            'serial' => 'nullable|string|max:100',
            'product_id' => 'nullable|integer|exists:products,id',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = SerialNumber::with(['product', 'outlet'])
            ->whereIn('outlet_id', $this->outletIds($request))
            ->latest();
        if ($request->filled('serial')) {
            $query->where('serial_number', 'like', "%{$request->serial}%");
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function giftCards(Request $request): JsonResponse
    {
        $request->validate(['per_page' => 'nullable|integer|min:1']);

        $query = GiftCard::where('status', 'active')
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->toDateString()))
            ->latest();

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function customerGroups(Request $request): JsonResponse
    {
        return response()->json(['data' => CustomerGroup::orderBy('min_spent')->get()]);
    }

    public function salesTargets(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'laporan')) {
            return $deny;
        }

        $rows = SalesTarget::with('outlet')
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->whereIn('outlet_id', $this->outletIds($request))
            ->get();

        return response()->json(['data' => $rows]);
    }
}
