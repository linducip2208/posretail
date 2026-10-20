<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\DiscountTemplate;
use App\Models\Installment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceExtraController extends Controller
{
    use ShopApi;

    public function discounts(Request $request): JsonResponse
    {
        $request->validate(['per_page' => 'nullable|integer|min:1']);

        $query = DiscountTemplate::where('active', true)
            ->where(fn ($q) => $q->whereNull('outlet_id')->orWhereIn('outlet_id', $this->outletIds($request)))
            ->latest();

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function installments(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'transaksi')) {
            return $deny;
        }
        $request->validate([
            'status' => 'nullable|string|max:30',
            'outlet_id' => 'nullable|integer|exists:outlets,id',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = Installment::with(['order'])
            ->whereHas('order', fn ($q) => $q->whereIn('outlet_id', $this->outletIds($request)))
            ->latest();
        foreach (['status'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->input($f));
            }
        }
        if ($request->filled('outlet_id')) {
            $oid = (int) $request->outlet_id;
            if (! in_array($oid, $this->outletIds($request), true)) {
                return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
            }
            $query->whereHas('order', fn ($q) => $q->where('outlet_id', $oid));
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function payInstallment(Request $request, Installment $installment): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'transaksi', 'create')) {
            return $deny;
        }
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:1000000000',
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
        ]);

        $order = $installment->order;
        if (! $order || ! in_array((int) $order->outlet_id, $this->outletIds($request), true)) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }

        \App\Models\Payment::create([
            'order_id' => $order->id,
            'payment_method_id' => $data['payment_method_id'],
            'amount' => $data['amount'],
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        $paid = (float) \App\Models\Payment::where('order_id', $order->id)
            ->whereIn('status', ['success', 'confirmed', 'completed'])->sum('amount');
        $remaining = max(0, (float) $order->total_amount - $paid);
        $order->update([
            'payment_status' => $remaining <= 0 ? 'paid' : 'partial',
            'remaining_amount' => $remaining,
        ]);

        return response()->json(['data' => [
            'installment_id' => $installment->id,
            'paid_total' => $paid,
            'remaining' => $remaining,
            'payment_status' => $order->fresh()->payment_status,
        ]]);
    }
}
