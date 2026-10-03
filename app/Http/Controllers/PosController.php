<?php

namespace App\Http\Controllers;

use App\Models\GiftCard;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SystemSetting;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $outlets = auth()->user()?->accessibleOutlets()?->get();
        $paymentMethods = PaymentMethod::where('active', true)->get();
        $taxPercent = (float) (SystemSetting::getValue('tax_percent', '0'));
        $appName = SystemSetting::getAppName();
        $appLogo = SystemSetting::getLogoUrl();
        $receiptFooter = SystemSetting::getValue('receipt_footer', 'Terima kasih telah berbelanja!');
        $storeAddress = SystemSetting::getValue('store_address', '');
        $storePhone = SystemSetting::getValue('store_phone', '');
        $receiptShowLogo = SystemSetting::getBool('receipt_show_logo', true);
        $receiptShowName = SystemSetting::getBool('receipt_show_name', true);
        $receiptShowAddress = SystemSetting::getBool('receipt_show_address', true);
        $receiptShowPhone = SystemSetting::getBool('receipt_show_phone', true);
        $receiptShowFooter = SystemSetting::getBool('receipt_show_footer', true);
        $orderTypes = SystemSetting::getOrderTypes();

        return view('pos.index', compact('outlets', 'paymentMethods', 'taxPercent', 'appName', 'appLogo', 'receiptFooter', 'storeAddress', 'storePhone', 'receiptShowLogo', 'receiptShowName', 'receiptShowAddress', 'receiptShowPhone', 'receiptShowFooter', 'orderTypes'));
    }

    public function products(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'unit', 'variants'])
            ->where('active', true);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%")
                    ->orWhere('barcode', $request->search);
            });
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderByRaw('current_stock <= 0')->orderBy('name')->paginate(24);

        return response()->json($products);
    }

    public function barcode(string $barcode): JsonResponse
    {
        $variant = ProductVariant::with('product.category', 'product.unit')
            ->where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->first();

        if ($variant) {
            return response()->json([
                'id' => $variant->product_id,
                'variant_id' => $variant->id,
                'variant_name' => $variant->name,
                'name' => $variant->product->name.' ('.$variant->name.')',
                'sku' => $variant->sku,
                'barcode' => $variant->barcode,
                'selling_price' => $variant->selling_price ?: $variant->product->selling_price,
                'current_stock' => $variant->current_stock,
                'serial_tracking' => $variant->product->serial_tracking ?? 'none',
                'category' => $variant->product->category,
                'unit' => $variant->product->unit,
            ]);
        }

        $product = Product::with(['category', 'unit'])
            ->where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json($product);
    }

    public function receipt(Request $request, int $id): View
    {
        $order = Order::with(['orderItems.product', 'orderItems.productVariant', 'payments.paymentMethod', 'customer', 'outlet', 'user'])
            ->findOrFail($id);

        // Batasi: kasir hanya struk outletnya
        $user = auth()->user();
        if ($user && ! in_array($order->outlet_id, $user->getAccessibleOutletIds())) {
            abort(403, 'Tidak ada akses ke struk ini.');
        }

        preg_match('/\[promo:(.*?)\]/', (string) $order->notes, $m);

        $orderData = [
            'order_number' => $order->order_number,
            'queue_number' => $order->queue_number,
            'created_at' => $order->created_at,
            'subtotal' => $order->subtotal,
            'discount_amount' => $order->discount_amount,
            'promo_name' => $m[1] ?? null,
            'tax_amount' => $order->tax_amount,
            'total_amount' => $order->total_amount,
            'remaining_amount' => $order->remaining_amount,
            'customer' => $order->customer ? ['name' => $order->customer->name] : null,
            'items' => $order->orderItems->map(fn ($i) => [
                'product' => ['name' => $i->product?->name],
                'variant' => $i->productVariant?->name,
                'quantity' => $i->quantity,
                'unit_price' => $i->unit_price,
                'subtotal' => $i->subtotal,
            ])->toArray(),
            'payments' => $order->payments->map(fn ($p) => [
                'amount' => $p->amount,
                'method' => $p->paymentMethod?->name ?? 'Bayar',
            ])->toArray(),
        ];

        $cashier = $order->user?->name ?? '-';
        $outlet = $order->outlet?->name ?? 'Outlet';

        return view('prints.receipt', [
            'order' => $orderData,
            'cashier' => $cashier,
            'outlet' => $outlet,
            'size' => in_array($request->query('size'), ['58', '80']) ? $request->query('size') : '80',
            'isReprint' => $request->boolean('reprint') || $request->query('reprint') === '1',
        ]);
    }

    public function checkout(Request $request, CheckoutService $checkout): JsonResponse
    {
        $validTypes = SystemSetting::getValidOrderTypeValues();
        $request->validate([
            'items' => 'required|array|min:1|max:200',
            'items.*.id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1|max:1000',
            // price dari client diabaikan — harga server yang dipakai
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.serial_numbers' => 'nullable|array',
            'outlet_id' => 'required|integer|exists:outlets,id',
            'order_type' => 'nullable|in:'.$validTypes,
            'customer_id' => 'nullable|integer|exists:customers,id',
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
            'paid_amount' => 'required|numeric|min:0|max:1000000000',
            'use_tax' => 'nullable|boolean',
            'voucher_code' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();
        if ($user && ! in_array($request->outlet_id, $user->getAccessibleOutletIds())) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $items = collect($request->items)->map(fn ($i) => [
            'product_id' => (int) $i['id'],
            'product_variant_id' => $i['variant_id'] ?? null,
            'quantity' => (int) $i['qty'],
            'discount_percent' => 0,
            'serial_numbers' => $i['serial_numbers'] ?? [],
        ])->toArray();

        $order = $checkout->checkout([
            'outlet_id' => (int) $request->outlet_id,
            'items' => $items,
            'payments' => [['payment_method_id' => (int) $request->payment_method_id, 'amount' => (float) $request->paid_amount]],
            'customer_id' => is_numeric($request->customer_id) ? (int) $request->customer_id : null,
            'order_type' => $request->order_type,
            'order_notes' => $request->order_notes,
            'notes' => $request->notes,
            'voucher_code' => $request->voucher_code,
            'use_tax' => $request->boolean('use_tax', true),
            'deposit_amount' => (float) ($request->deposit_amount ?? 0),
            'is_installment' => (bool) ($request->is_installment ?? false),
            'installment_period' => $request->installment_period,
            'installment_count' => (int) ($request->installment_count ?? 1),
        ], (int) auth()->id());

        return response()->json([
            'success' => true,
            'id' => $order->id,
            'order_number' => $order->order_number,
            'queue_number' => $order->queue_number,
            'total' => $order->total_amount,
            'change' => $request->paid_amount - $order->total_amount,
        ]);
    }

    public function validateVoucher(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:100',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $giftCard = GiftCard::where('code', trim((string) $request->code))->first();
        if (! $giftCard || ! $giftCard->isValid()) {
            return response()->json(['valid' => false, 'message' => 'Voucher tidak valid/kadaluarsa.'], 422);
        }

        $subtotal = (float) $request->subtotal;
        if ($subtotal < (float) $giftCard->min_purchase) {
            return response()->json(['valid' => false, 'message' => 'Minimal pembelian belum terpenuhi.'], 422);
        }

        $discount = $giftCard->type === 'discount_percent'
            ? round($subtotal * (float) $giftCard->value / 100, 2)
            : min((float) $giftCard->remaining_balance, $subtotal);

        return response()->json([
            'valid' => true,
            'code' => $giftCard->code,
            'type' => $giftCard->type,
            'discount' => $discount,
        ]);
    }

    public function display(Request $request): JsonResponse
    {
        $latest = Order::with(['orderItems.product', 'orderItems.productVariant', 'outlet'])
            ->excludeCancelled()
            ->whereDate('created_at', today())
            ->when($request->outlet_id, fn ($q) => $q->where('outlet_id', $request->outlet_id))
            ->latest()
            ->first();

        if (! $latest) {
            return response()->json(['items' => [], 'total' => 0, 'queue_number' => null]);
        }

        return response()->json([
            'order_id' => $latest->id,
            'queue_number' => $latest->queue_number,
            'total' => $latest->total_amount,
            'outlet_name' => $latest->outlet?->name,
            'items' => $latest->orderItems->map(fn ($i) => [
                'name' => $i->product?->name ?? '-',
                'variant' => $i->productVariant?->name,
                'qty' => $i->quantity,
                'price' => $i->unit_price,
                'subtotal' => $i->subtotal,
            ])->toArray(),
        ]);
    }
}
