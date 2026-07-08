<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $query = Product::with(['category', 'unit'])
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
        $product = Product::with(['category', 'unit'])
            ->where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->first();

        if (!$product) {
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
            'items' => $order->items->map(fn($i) => [
                'product' => ['name' => $i->product?->name],
                'quantity' => $i->quantity,
                'unit_price' => $i->unit_price,
                'subtotal' => $i->subtotal,
            ])->toArray(),
            'payments' => $order->payments->map(fn($p) => ['amount' => $p->amount])->toArray(),
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
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'outlet_id' => 'required|exists:outlets,id',
            'order_type' => 'nullable|in:' . $validTypes,
            'payment_method_id' => 'required|exists:payment_methods,id',
            'paid_amount' => 'required|numeric|min:0',
            'use_tax' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        if ($user && !in_array($request->outlet_id, $user->getAccessibleOutletIds())) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $order = DB::transaction(function () use ($request) {
            $subtotal = 0;
            $taxPercent = 0;
            if ($request->boolean('use_tax', true)) {
                $taxPercent = (float) (\App\Models\SystemSetting::getValue('tax_percent', '0'));
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

            $todayCount = Order::where('outlet_id', $request->outlet_id)
                ->whereDate('created_at', today())
                ->count();
            $queueNumber = str_pad($todayCount + 1, 3, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'customer_id' => $request->customer_id,
                'outlet_id' => $request->outlet_id,
                'user_id' => auth()->id(),
                'table_id' => $request->table_id,
                'order_type' => $request->order_type ?? SystemSetting::getDefaultOrderType(),
                'queue_number' => $queueNumber,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
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
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'discount_percent' => 0,
                    'discount_amount' => 0,
                    'subtotal' => $item['price'] * $item['qty'],
                ]);

                Product::find($item['id'])->decrement('current_stock', $item['qty']);

                StockMovement::create([
                    'product_id' => $item['id'],
                    'outlet_id' => $request->outlet_id,
                    'type' => 'out',
                    'quantity' => $item['qty'],
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'notes' => 'Penjualan #' . $order->order_number,
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $request->payment_method_id,
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
}
