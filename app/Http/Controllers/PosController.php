<?php

namespace App\Http\Controllers;

use App\Models\GiftCard;
use App\Models\GiftCardUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SerialNumber;
use App\Models\StockMovement;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

    public function receipt(int $id): View
    {
        $order = Order::with(['items.product', 'payments', 'customer', 'outlet', 'user'])
            ->findOrFail($id);

        $orderData = [
            'order_number' => $order->order_number,
            'created_at' => $order->created_at,
            'subtotal' => $order->subtotal,
            'discount_amount' => $order->discount_amount,
            'tax_amount' => $order->tax_amount,
            'total_amount' => $order->total_amount,
            'customer' => $order->customer ? ['name' => $order->customer->name] : null,
            'items' => $order->items->map(fn ($i) => [
                'product' => ['name' => $i->product?->name],
                'quantity' => $i->quantity,
                'unit_price' => $i->unit_price,
                'subtotal' => $i->subtotal,
            ])->toArray(),
            'payments' => $order->payments->map(fn ($p) => ['amount' => $p->amount])->toArray(),
        ];

        $cashier = $order->user?->name ?? '-';
        $outlet = $order->outlet?->name ?? 'Outlet';

        return view('prints.receipt', ['order' => $orderData, 'cashier' => $cashier, 'outlet' => $outlet]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $validTypes = SystemSetting::getValidOrderTypeValues();
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'outlet_id' => 'required|integer|exists:outlets,id',
            'order_type' => 'nullable|in:'.$validTypes,
            'customer_id' => 'nullable|integer|exists:customers,id',
            'table_id' => 'nullable|integer|exists:tables,id',
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
            'paid_amount' => 'required|numeric|min:0',
            'use_tax' => 'nullable|boolean',
            'voucher_code' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();
        if ($user && ! in_array($request->outlet_id, $user->getAccessibleOutletIds())) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $this->validateSerialNumbers($request->items);

        $order = DB::transaction(function () use ($request) {
            $subtotal = 0;
            $taxPercent = 0;
            if ($request->boolean('use_tax', true)) {
                $taxPercent = (float) (SystemSetting::getValue('tax_percent', '0'));
            }
            $items = [];

            foreach ($request->items as $item) {
                $lineSubtotal = $item['price'] * $item['qty'];
                $subtotal += $lineSubtotal;
                $items[] = $item;
            }

            $taxAmount = $subtotal * $taxPercent / 100;
            $totalAmount = $subtotal + $taxAmount;
            $paidAmount = $request->paid_amount;
            $deposit = $request->deposit_amount ?? 0;

            $discountAmount = 0;
            $voucher = null;
            if ($request->filled('voucher_code')) {
                $voucher = $this->resolveVoucher((string) $request->voucher_code, $subtotal);
                $discountAmount = $voucher['discount'];
                $voucher = $voucher['giftCard'];
            }

            $taxAmount = max(0, $subtotal - $discountAmount) * $taxPercent / 100;
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            $todayCount = Order::where('outlet_id', $request->outlet_id)
                ->whereDate('created_at', today())
                ->excludeCancelled()
                ->count();
            $queueNumber = str_pad($todayCount + 1, 3, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number' => 'ORD-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -6)),
                'customer_id' => is_numeric($request->customer_id) ? (int) $request->customer_id : null,
                'outlet_id' => (int) $request->outlet_id,
                'user_id' => auth()->id(),
                'table_id' => $request->table_id ? (int) $request->table_id : null,
                'order_type' => $request->order_type ?? SystemSetting::getDefaultOrderType(),
                'queue_number' => $queueNumber,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'deposit_amount' => $deposit,
                'remaining_amount' => max(0, $totalAmount - $paidAmount - $deposit),
                'is_installment' => $request->is_installment ?? false,
                'installment_period' => $request->installment_period,
                'installment_count' => $request->installment_count ?? 1,
                'payment_status' => $paidAmount >= $totalAmount ? 'paid' : 'partial',
                'order_status' => 'completed',
                'order_notes' => $request->order_notes,
                'notes' => $request->notes,
            ]);

            foreach ($items as $item) {
                $product = Product::find($item['id']);

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'discount_percent' => 0,
                    'discount_amount' => 0,
                    'subtotal' => $item['price'] * $item['qty'],
                    'serial_number' => implode(',', $item['serial_numbers'] ?? []),
                ]);

                $warrantyMonths = (int) ($product->warranty_months ?? 0);

                foreach ($item['serial_numbers'] ?? [] as $sn) {
                    SerialNumber::where('product_id', $item['id'])
                        ->where('serial_number', $sn)
                        ->where('status', 'in_stock')
                        ->update([
                            'status' => 'sold',
                            'order_item_id' => $orderItem->id,
                            'outlet_id' => $request->outlet_id,
                            'warranty_expires_at' => $warrantyMonths > 0
                                ? now()->addMonths($warrantyMonths)->toDateString()
                                : null,
                        ]);
                }

                if (isset($item['variant_id'])) {
                    ProductVariant::find($item['variant_id'])?->decrement('current_stock', $item['qty']);
                }

                $product->decrement('current_stock', $item['qty']);

                StockMovement::create([
                    'product_id' => $item['id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'outlet_id' => $request->outlet_id,
                    'type' => 'out',
                    'quantity' => $item['qty'],
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'notes' => 'Penjualan #'.$order->order_number,
                ]);
            }

            if ($voucher) {
                GiftCardUsage::create([
                    'gift_card_id' => $voucher->id,
                    'order_id' => $order->id,
                    'amount_used' => $discountAmount,
                ]);

                if ($voucher->type === 'nominal') {
                    $voucher->decrement('remaining_balance', $discountAmount);
                }
                $voucher->increment('used_count');
                if ($voucher->used_count >= $voucher->max_usage) {
                    $voucher->update(['status' => 'used']);
                }
            }

            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => (int) $request->payment_method_id,
                'amount' => $paidAmount,
                'status' => 'success',
                'paid_at' => now(),
            ]);

            return $order;
        });

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

        $resolved = $this->resolveVoucher((string) $request->code, (float) $request->subtotal);

        return response()->json([
            'valid' => true,
            'code' => $resolved['giftCard']->code,
            'type' => $resolved['giftCard']->type,
            'discount' => $resolved['discount'],
        ]);
    }

    protected function validateSerialNumbers(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['id']);
            $tracking = $product?->serial_tracking ?? 'none';

            if ($tracking === 'none') {
                continue;
            }

            $serials = $item['serial_numbers'] ?? [];
            $qty = (int) $item['qty'];

            if ($tracking === 'required' && count($serials) !== $qty) {
                throw ValidationException::withMessages([
                    'items' => "IMEI wajib diisi lengkap untuk \"{$product->name}\" (jumlah {$qty}).",
                ]);
            }

            if ($tracking === 'optional' && count($serials) > 0 && count($serials) !== $qty) {
                throw ValidationException::withMessages([
                    'items' => "Jumlah IMEI untuk \"{$product->name}\" harus sama dengan jumlah barang ({$qty}).",
                ]);
            }

            foreach ($serials as $sn) {
                $exists = SerialNumber::where('product_id', $item['id'])
                    ->where('serial_number', $sn)
                    ->where('status', 'in_stock')
                    ->exists();

                if (! $exists) {
                    throw ValidationException::withMessages([
                        'items' => "IMEI \"{$sn}\" tidak ditemukan atau sudah terjual.",
                    ]);
                }
            }
        }
    }

    protected function resolveVoucher(string $code, float $subtotal): array
    {
        $giftCard = GiftCard::where('code', trim($code))->first();

        if (! $giftCard || ! $giftCard->isValid()) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Voucher tidak valid atau sudah kadaluarsa.',
            ]);
        }

        if ($subtotal < (float) $giftCard->min_purchase) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Minimal pembelian Rp '.number_format((float) $giftCard->min_purchase, 0, ',', '.').' untuk menggunakan voucher ini.',
            ]);
        }

        if ($giftCard->type === 'discount_percent') {
            $discount = round($subtotal * (float) $giftCard->value / 100, 2);
        } else {
            $discount = min((float) $giftCard->remaining_balance, $subtotal);
        }

        return [
            'giftCard' => $giftCard,
            'discount' => $discount,
        ];
    }

    public function display(Request $request): JsonResponse
    {
        $latest = Order::with(['items.product', 'outlet'])
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
            'items' => $latest->items->map(fn ($i) => [
                'name' => $i->product?->name ?? '-',
                'variant' => $i->productVariant?->name,
                'qty' => $i->quantity,
                'price' => $i->unit_price,
                'subtotal' => $i->subtotal,
            ])->toArray(),
        ]);
    }
}
