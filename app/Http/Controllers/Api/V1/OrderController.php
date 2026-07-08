<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'customer_id' => 'nullable|exists:customers,id',
            'outlet_id' => 'required|exists:outlets,id',
            'order_type' => 'nullable|in:' . SystemSetting::getValidOrderTypeValues(),
            'table_id' => 'nullable|exists:tables,id',
            'employee_id' => 'nullable|exists:users,id',
            'deposit_amount' => 'nullable|numeric|min:0',
            'is_installment' => 'nullable|boolean',
            'installment_period' => 'nullable|in:weekly,biweekly,monthly',
            'installment_count' => 'nullable|integer|min:1',
            'order_notes' => 'nullable|string',
            'payments' => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $outletIds = $request->user()->getAccessibleOutletIds();
        if (!empty($outletIds) && !in_array((int) $request->outlet_id, $outletIds)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $order = DB::transaction(function () use ($request) {
            $subtotal = 0;
            $discountAmount = 0;

            foreach ($request->items as $item) {
                $lineSubtotal = $item['unit_price'] * $item['quantity'];
                $lineDiscount = ($lineSubtotal * ($item['discount_percent'] ?? 0)) / 100;
                $subtotal += $lineSubtotal;
                $discountAmount += $lineDiscount;
            }

            $totalAmount = $subtotal - $discountAmount;

            $totalPaid = collect($request->payments)->sum('amount');
            $remainingAmount = $totalAmount - $totalPaid - ($request->deposit_amount ?? 0);

            $todayCount = Order::where('outlet_id', $request->outlet_id)
                ->whereDate('created_at', today())
                ->count();
            $queueNumber = str_pad($todayCount + 1, 3, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'customer_id' => $request->customer_id,
                'outlet_id' => $request->outlet_id,
                'user_id' => $request->user()->id,
                'employee_id' => $request->employee_id,
                'table_id' => $request->table_id,
                'order_type' => $request->order_type ?? SystemSetting::getDefaultOrderType(),
                'queue_number' => $queueNumber,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => 0,
                'total_amount' => $totalAmount,
                'deposit_amount' => $request->deposit_amount ?? 0,
                'remaining_amount' => max(0, $totalAmount - $totalPaid - ($request->deposit_amount ?? 0)),
                'is_installment' => $request->is_installment ?? false,
                'installment_period' => $request->installment_period,
                'installment_count' => $request->installment_count ?? 1,
                'payment_status' => 'pending',
                'order_status' => 'completed',
                'order_notes' => $request->order_notes,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $lineSubtotal = $item['unit_price'] * $item['quantity'];
                $lineDiscount = ($lineSubtotal * ($item['discount_percent'] ?? 0)) / 100;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'discount_amount' => $lineDiscount,
                    'subtotal' => $lineSubtotal - $lineDiscount,
                ]);

                if (!empty($item['product_variant_id'])) {
                    ProductVariant::find($item['product_variant_id'])->decrement('current_stock', $item['quantity']);
                } else {
                    Product::find($item['product_id'])->decrement('current_stock', $item['quantity']);
                }

                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'outlet_id' => $request->outlet_id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'notes' => 'Penjualan #' . $order->order_number,
                ]);
            }

            foreach ($request->payments as $paymentData) {
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => $paymentData['payment_method_id'],
                    'amount' => $paymentData['amount'],
                    'status' => 'success',
                    'paid_at' => now(),
                ]);
            }

            $totalPaid = collect($request->payments)->sum('amount');
            $order->update([
                'payment_status' => $totalPaid >= $totalAmount ? 'paid' : 'partial',
            ]);

            return $order;
        });

        $order->load(['orderItems.product', 'payments.paymentMethod']);

        return response()->json(['data' => $this->formatOrder($order)], 201);
    }

    public function today(Request $request): JsonResponse
    {
        $outletIds = $request->user()->getAccessibleOutletIds();
        $query = Order::with(['orderItems.product', 'payments', 'user', 'customer', 'outlet'])
            ->whereDate('created_at', today())
            ->latest();

        if ($request->outlet_id && in_array((int) $request->outlet_id, $outletIds)) {
            $query->where('outlet_id', $request->outlet_id);
        } elseif (!empty($outletIds)) {
            $query->whereIn('outlet_id', $outletIds);
        }

        return response()->json(['data' => $query->get()->map(fn ($o) => $this->formatOrder($o))]);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['orderItems.product', 'orderItems.productVariant', 'payments.paymentMethod', 'user', 'customer', 'outlet']);

        return response()->json(['data' => $this->formatOrder($order)]);
    }

    private function formatOrder(Order $order): array
    {
        $data = $order->toArray();
        if ($order->relationLoaded('orderItems')) {
            $data['items'] = $order->orderItems->toArray();
            unset($data['order_items']);
        }
        if ($order->relationLoaded('customer') && $order->customer) {
            $data['customer_name'] = $order->customer->name;
        }
        if ($order->relationLoaded('outlet') && $order->outlet) {
            $data['outlet_name'] = $order->outlet->name;
        }
        if ($order->relationLoaded('user') && $order->user) {
            $data['cashier_name'] = $order->user->name;
        }
        return $data;
    }
}
