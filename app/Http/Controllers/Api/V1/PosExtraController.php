<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CashDrawerTransaction;
use App\Models\Customer;
use App\Models\CustomerDeposit;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Retur;
use App\Models\ReturnItem;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Fitur POS P2: refund parsial, shift open/close, deposit.
 */
class PosExtraController extends Controller
{
    /** Refund parsial per item → Retur + restore stok via Retur::applyReturn. */
    public function refund(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1|max:200',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        if (! $user->hasPermission('*') && ! in_array((int) $order->outlet_id, $user->getAccessibleOutletIds(), true)) {
            return response()->json(['message' => 'Tidak ada akses.'], 403);
        }
        if (! $user->can('delete', $order)) {
            return response()->json(['message' => 'Butuh izin hapus-transaksi untuk refund.'], 403);
        }

        $retur = DB::transaction(function () use ($request, $order, $user) {
            $order->loadMissing('orderItems');
            $map = $order->orderItems->keyBy('id');

            $r = Retur::create([
                'order_id' => $order->id,
                'outlet_id' => $order->outlet_id,
                'user_id' => $user->id,
                'type' => 'customer_return',
                'total_amount' => 0,
                'reason' => $request->reason ?? 'Refund parsial POS',
                'status' => 'pending',
            ]);

            foreach ($request->items as $row) {
                $item = $map->get($row['order_item_id']);
                if (! $item || (int) $row['quantity'] < 1 || (int) $row['quantity'] > $item->quantity) {
                    throw ValidationException::withMessages(['items' => 'Qty refund tidak valid.']);
                }
                ReturnItem::create([
                    'return_id' => $r->id,
                    'product_id' => $item->product_id,
                    'quantity' => (int) $row['quantity'],
                    'unit_price' => $item->unit_price,
                ]);
            }

            $r->update(['status' => 'completed']); // trigger applyReturn (restore stok + movement)
            $r->refresh();

            // Tandai payment refund proporsional (sederhana: catat cash_out shift)
            $activeShift = Shift::where('outlet_id', $order->outlet_id)->where('status', 'open')->latest('started_at')->first();
            CashDrawerTransaction::create([
                'shift_id' => $activeShift?->id,
                'order_id' => $order->id,
                'type' => 'cash_out',
                'amount' => $r->total_amount,
                'payment_method' => null,
                'notes' => 'Refund parsial '.$r->return_number,
            ]);

            return $r;
        });

        return response()->json(['data' => $retur->load('returnItems')], 201);
    }

    /** Buka shift kasir. */
    public function openShift(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'starting_cash' => 'required|numeric|min:0|max:1000000000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        if (! $user->hasPermission('*') && ! in_array((int) $request->outlet_id, $user->getAccessibleOutletIds(), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $exists = Shift::where('outlet_id', $request->outlet_id)->where('status', 'open')->exists();
        if ($exists) {
            return response()->json(['message' => 'Masih ada shift terbuka di outlet ini.'], 422);
        }

        $shift = Shift::create([
            'outlet_id' => $request->outlet_id,
            'user_id' => $request->user()->id,
            'started_at' => now(),
            'starting_cash' => $request->starting_cash,
            'status' => 'open',
            'notes' => $request->notes,
        ]);

        return response()->json(['data' => $shift], 201);
    }

    /** Tutup shift: hitung kas harapan vs aktual. */
    public function closeShift(Request $request, Shift $shift): JsonResponse
    {
        $request->validate(['ending_cash' => 'required|numeric|min:0|max:1000000000']);

        $user = $request->user();
        if (! $user->hasPermission('*') && ! in_array((int) $shift->outlet_id, $user->getAccessibleOutletIds(), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        if ($shift->status !== 'open') {
            return response()->json(['message' => 'Shift sudah ditutup.'], 422);
        }

        $cashIn = (float) $shift->cashDrawerTransactions()->where('type', 'cash_in')->sum('amount');
        $cashOut = (float) $shift->cashDrawerTransactions()->where('type', 'cash_out')->sum('amount');
        $orderCash = (float) Payment::whereHas('order', fn ($q) => $q->where('outlet_id', $shift->outlet_id)
            ->whereBetween('created_at', [$shift->started_at, now()]))->sum('amount');
        $expected = (float) $shift->starting_cash + $cashIn + $orderCash - $cashOut;

        $shift->update([
            'ended_at' => now(),
            'ending_cash' => $request->ending_cash,
            'expected_cash' => $expected,
            'difference' => (float) $request->ending_cash - $expected,
            'status' => 'closed',
        ]);

        return response()->json(['data' => $shift->fresh()]);
    }

    /** Topup / pakai deposit pelanggan (hutang-piutang sederhana). */
    public function deposit(Request $request, Customer $customer): JsonResponse
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'type' => 'required|in:topup,use,refund',
            'amount' => 'required|numeric|min:1|max:1000000000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        if (! $user->hasPermission('*') && ! in_array((int) $request->outlet_id, $user->getAccessibleOutletIds(), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $amount = (float) $request->amount;
        if ($request->type === 'topup') {
            $customer->increment('deposit_balance', $amount);
        } elseif ($request->type === 'use') {
            if ((float) $customer->deposit_balance < $amount) {
                return response()->json(['message' => 'Saldo deposit kurang.'], 422);
            }
            $customer->decrement('deposit_balance', $amount);
        } else {
            $customer->increment('deposit_balance', $amount);
        }

        $log = CustomerDeposit::create([
            'customer_id' => $customer->id,
            'outlet_id' => $request->outlet_id,
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'amount' => $amount,
            'balance_after' => $customer->fresh()->deposit_balance,
            'reference' => 'pos_manual',
            'notes' => $request->notes,
        ]);

        return response()->json(['data' => $log], 201);
    }
}
