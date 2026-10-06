<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CheckoutService;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Token harus punya ability 'pos-access' atau role lama.
     * Return response 403 atau null jika lolos. Kompatibel token lama [role].
     */
    protected function ensurePosAbility(Request $request): ?JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();
        if (! $token) {
            return null; // sesi web / guard lain
        }
        if ($token->can('pos-access') || ($user->role && $token->can($user->role))) {
            return null;
        }

        return response()->json(['message' => 'Token tidak memiliki akses POS. Login ulang di aplikasi kasir.'], 403);
    }

    public function store(Request $request, CheckoutService $checkout): JsonResponse
    {
        if ($denied = $this->ensurePosAbility($request)) {
            return $denied;
        }
        $request->validate([
            'items' => 'required|array|min:1|max:200',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:1000',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            // unit_price tetap diterima untuk kompatibilitas Flutter lama, tapi DIABAIKAN —
            // harga selalu diambil dari server (anti price-tampering).
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.serial_numbers' => 'nullable|array|max:1000',
            'items.*.serial_numbers.*' => 'nullable|string|max:100',
            'customer_id' => 'nullable|exists:customers,id',
            'outlet_id' => 'required|exists:outlets,id',
            'order_type' => 'nullable|in:' . SystemSetting::getValidOrderTypeValues(),
            'employee_id' => 'nullable|exists:users,id',
            'deposit_amount' => 'nullable|numeric|min:0|max:1000000000',
            'voucher_code' => 'nullable|string|max:100',
            'is_installment' => 'nullable|boolean',
            'installment_period' => 'nullable|in:weekly,biweekly,monthly',
            'installment_count' => 'nullable|integer|min:1|max:60',
            'order_notes' => 'nullable|string|max:2000',
            'payments' => 'required|array|min:1|max:10',
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount' => 'required|numeric|min:0|max:1000000000',
            'notes' => 'nullable|string|max:2000',
        ]);

        $outletIds = $request->user()->getAccessibleOutletIds();
        if (! $request->user()->hasPermission('*') && ! in_array((int) $request->outlet_id, $outletIds, true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $items = collect($request->items)->map(fn ($i) => [
            'product_id' => (int) $i['product_id'],
            'product_variant_id' => $i['product_variant_id'] ?? null,
            'quantity' => (int) $i['quantity'],
            'discount_percent' => (float) ($i['discount_percent'] ?? 0),
            'serial_numbers' => $i['serial_numbers'] ?? [],
        ])->toArray();

        $order = $checkout->checkout([
            'outlet_id' => (int) $request->outlet_id,
            'items' => $items,
            'payments' => $request->payments,
            'customer_id' => $request->customer_id,
            'employee_id' => $request->employee_id,
            'order_type' => $request->order_type,
            'order_notes' => $request->order_notes,
            'notes' => $request->notes,
            'voucher_code' => $request->voucher_code ?? null,
            'use_tax' => false, // API mobile: pajak dihitung di server via setting jika diperlukan
            'deposit_amount' => (float) ($request->deposit_amount ?? 0),
            'is_installment' => (bool) ($request->is_installment ?? false),
            'installment_period' => $request->installment_period,
            'installment_count' => (int) ($request->installment_count ?? 1),
        ], (int) $request->user()->id);

        $order->load(['orderItems.product', 'payments.paymentMethod']);

        return response()->json(['data' => $this->formatOrder($order)], 201);
    }

    public function today(Request $request): JsonResponse
    {
        $outletIds = $request->user()->getAccessibleOutletIds();
        $query = Order::with(['orderItems.product', 'payments', 'user', 'customer', 'outlet'])
            ->excludeCancelled()
            ->whereDate('created_at', today())
            ->latest();

        if ($request->outlet_id && ($request->user()->hasPermission('*') || in_array((int) $request->outlet_id, $outletIds, true))) {
            $query->where('outlet_id', $request->outlet_id);
        } elseif (! $request->user()->hasPermission('*')) {
            $query->whereIn('outlet_id', $outletIds);
        }

        return response()->json(['data' => $query->get()->map(fn ($o) => $this->formatOrder($o))]);
    }

    /**
     * Riwayat order per periode untuk layar Laporan Flutter.
     * GET /api/v1/orders/history?start_date=Y-m-d&end_date=Y-m-d&outlet_id=
     */
    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);

        $start = $request->start_date ?? now()->subDays(6)->format('Y-m-d');
        $end = $request->end_date ?? now()->format('Y-m-d');

        if (now()->parse($start)->diffInDays(now()->parse($end)) > 93) {
            return response()->json(['message' => 'Rentang tanggal maksimal 93 hari.'], 422);
        }

        $outletIds = $request->user()->getAccessibleOutletIds();
        $query = Order::with(['orderItems.product', 'payments', 'user', 'customer', 'outlet'])
            ->excludeCancelled()
            ->whereBetween('created_at', [$start, $end.' 23:59:59'])
            ->latest()
            ->limit(500);

        if ($request->outlet_id && ($request->user()->hasPermission('*') || in_array((int) $request->outlet_id, $outletIds, true))) {
            $query->where('outlet_id', $request->outlet_id);
        } elseif (! $request->user()->hasPermission('*')) {
            $query->whereIn('outlet_id', $outletIds);
        }

        return response()->json(['data' => $query->get()->map(fn ($o) => $this->formatOrder($o))]);
    }

    public function show(Order $order): JsonResponse
    {
        $user = request()->user();
        $accessibleOutletIds = $user->getAccessibleOutletIds();

        if (! $user->hasPermission('*') && ! in_array((int) $order->outlet_id, $accessibleOutletIds, true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke order ini.'], 403);
        }

        $order->load(['orderItems.product', 'orderItems.productVariant', 'payments.paymentMethod', 'user', 'customer', 'outlet']);

        return response()->json(['data' => $this->formatOrder($order)]);
    }

    /** Offline queue: Flutter kirim batch order saat kembali online (idempotent via client_uuid). */
    public function syncBatch(Request $request, CheckoutService $checkout): JsonResponse
    {
        if ($denied = $this->ensurePosAbility($request)) {
            return $denied;
        }
        $request->validate([
            'orders' => 'required|array|min:1|max:50',
            'orders.*.client_uuid' => 'required|string|max:100',
            'orders.*.outlet_id' => 'required|exists:outlets,id',
            'orders.*.items' => 'required|array|min:1|max:200',
            'orders.*.items.*.product_id' => 'required|exists:products,id',
            'orders.*.items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'orders.*.items.*.quantity' => 'required|integer|min:1|max:1000',
            'orders.*.items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'orders.*.payments' => 'required|array|min:1|max:10',
            'orders.*.payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'orders.*.payments.*.amount' => 'required|numeric|min:0|max:1000000000',
            'orders.*.customer_id' => 'nullable|exists:customers,id',
        ]);

        $user = $request->user();
        $outletIds = $user->getAccessibleOutletIds();
        $results = [];
        foreach ($request->orders as $entry) {
            $uuid = (string) $entry['client_uuid'];
            // Idempotency: lookup persis via kolom unique (bukan LIKE ke notes).
            $existing = Order::where('client_uuid', $uuid)->first();
            if ($existing) {
                $results[] = ['client_uuid' => $uuid, 'status' => 'duplicate', 'order_number' => $existing->order_number, 'id' => $existing->id];
                continue;
            }
            // Outlet check per order (batch tetap jalan untuk order lain).
            if (! $user->hasPermission('*') && ! in_array((int) $entry['outlet_id'], $outletIds, true)) {
                $results[] = ['client_uuid' => $uuid, 'status' => 'failed', 'message' => 'Tidak ada akses ke outlet ini.'];
                continue;
            }
            try {
                $items = collect($entry['items'])->map(fn ($i) => [
                    'product_id' => (int) $i['product_id'],
                    'product_variant_id' => $i['product_variant_id'] ?? null,
                    'quantity' => (int) $i['quantity'],
                    'discount_percent' => (float) ($i['discount_percent'] ?? 0),
                ])->toArray();
                $order = $checkout->checkout([
                    'client_uuid' => $uuid,
                    'outlet_id' => (int) $entry['outlet_id'],
                    'items' => $items,
                    'payments' => $entry['payments'],
                    'customer_id' => $entry['customer_id'] ?? null,
                    'order_type' => $entry['order_type'] ?? SystemSetting::getDefaultOrderType(),
                    'notes' => $entry['notes'] ?? null,
                    'use_tax' => false,
                ], (int) $user->id);
                $results[] = ['client_uuid' => $uuid, 'status' => 'created', 'order_number' => $order->order_number, 'id' => $order->id];
            } catch (ValidationException $e) {
                // Race: order dengan uuid sama terbuat bersamaan → kembalikan yang existing.
                $dup = Order::where('client_uuid', $uuid)->first();
                if ($dup) {
                    $results[] = ['client_uuid' => $uuid, 'status' => 'duplicate', 'order_number' => $dup->order_number, 'id' => $dup->id];
                } else {
                    $results[] = ['client_uuid' => $uuid, 'status' => 'failed', 'message' => $e->getMessage()];
                }
            } catch (\Throwable $e) {
                $dup = $e instanceof \Illuminate\Database\QueryException && str_contains($e->getMessage(), 'client_uuid')
                    ? Order::where('client_uuid', $uuid)->first()
                    : null;
                if ($dup) {
                    $results[] = ['client_uuid' => $uuid, 'status' => 'duplicate', 'order_number' => $dup->order_number, 'id' => $dup->id];
                } else {
                    $results[] = ['client_uuid' => $uuid, 'status' => 'failed', 'message' => $e->getMessage()];
                }
            }
        }

        return response()->json(['data' => $results]);
    }

    /** Split bill: pecah sebagian item ke order baru. */
    public function split(Request $request, Order $order, CheckoutService $checkout): JsonResponse
    {
        $request->validate([
            'moves' => 'required|array|min:1',
            'moves.*.order_item_id' => 'required|exists:order_items,id',
            'moves.*.quantity' => 'required|integer|min:1',
            'payments' => 'nullable|array|max:10',
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount' => 'required|numeric|min:0',
        ]);

        $user = $request->user();
        if (! $user->hasPermission('*') && ! in_array((int) $order->outlet_id, $user->getAccessibleOutletIds(), true)) {
            return response()->json(['message' => 'Tidak ada akses.'], 403);
        }

        $child = $checkout->splitBill($order, $request->moves, (int) $user->id, $request->payments ?? []);
        $child->load(['orderItems.product', 'payments']);

        return response()->json(['data' => $this->formatOrder($child)], 201);
    }

    private function formatOrder(Order $order): array
    {
        $data = $order->toArray();
        // Jangan bocorkan data internal ke kasir/Flutter
        unset($data['commission_amount']);
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
