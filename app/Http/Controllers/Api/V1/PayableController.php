<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\PayablePayment;
use App\Models\SupplierPayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayableController extends Controller
{
    use ShopApi;

    public function index(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'pembelian')) {
            return $deny;
        }
        $request->validate([
            'status' => 'nullable|string|max:30',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'overdue' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = SupplierPayable::with(['supplier', 'purchaseOrder'])->latest();
        foreach (['status', 'supplier_id'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->input($f));
            }
        }
        if ($request->boolean('overdue')) {
            $query->where('due_date', '<', now()->toDateString())->where('status', '!=', 'paid');
        }

        return $this->envelope($query->paginate($this->perPage($request)));
    }

    public function pay(Request $request, SupplierPayable $supplierPayable): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'pembelian', 'create')) {
            return $deny;
        }
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:1000000000',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'payment_date' => 'nullable|date',
        ]);

        $remaining = (float) $supplierPayable->total_amount - (float) $supplierPayable->paid_amount;
        if ($data['amount'] > $remaining) {
            return response()->json(['message' => 'Nominal melebihi sisa hutang Rp ' . number_format($remaining, 0, ',', '.')], 422);
        }

        DB::transaction(function () use ($supplierPayable, $data) {
            PayablePayment::create([
                'supplier_payable_id' => $supplierPayable->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
            ]);
            $paid = (float) $supplierPayable->paid_amount + (float) $data['amount'];
            $supplierPayable->update([
                'paid_amount' => $paid,
                'status' => $paid >= (float) $supplierPayable->total_amount ? 'paid' : 'partial',
            ]);
        });

        return response()->json(['data' => $supplierPayable->fresh()]);
    }
}
