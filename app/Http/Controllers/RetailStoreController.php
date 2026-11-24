<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

/**
 * Aksi simpan sederhana untuk form POS Retail (100% POS Retail, tanpa Filament).
 */
class RetailStoreController extends Controller
{
    public function storeProduct(Request $request)
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'current_stock' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $v['cost_price'] = $v['cost_price'] ?? 0;
        $v['current_stock'] = $v['current_stock'] ?? 0;
        $v['min_stock'] = $v['min_stock'] ?? 0;

        $product = Product::create($v);

        return redirect('/app/product-details?id='.$product->id)->with('success', 'Produk "'.$product->name.'" berhasil ditambahkan.');
    }

    public function storeCustomer(Request $request)
    {
        $request->merge(['email' => $request->input('email') ?: null]);
        $v = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'address' => ['nullable', 'string'],
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
        ]);

        $customer = Customer::create($v);

        return redirect('/app/customer-details?id='.$customer->id)->with('success', 'Pelanggan "'.$customer->name.'" berhasil didaftarkan.');
    }

    public function storePurchaseOrder(Request $request)
    {
        $v = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $total = collect($v['items'])->sum(fn ($i) => $i['quantity'] * $i['unit_price']);

        $po = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generateNumber(),
            'supplier_id' => $v['supplier_id'],
            'outlet_id' => Outlet::where('active', true)->value('id'),
            'user_id' => auth()->id(),
            'total_amount' => $total,
            'status' => 'draft',
            'notes' => $v['notes'] ?? null,
        ]);

        foreach ($v['items'] as $i) {
            $po->purchaseOrderItems()->create([
                'product_id' => $i['product_id'],
                'quantity' => $i['quantity'],
                'unit_price' => $i['unit_price'],
                'subtotal' => $i['quantity'] * $i['unit_price'],
            ]);
        }

        return redirect('/app/purchase-order-details?id='.$po->id)->with('success', 'PO '.$po->po_number.' berhasil dibuat.');
    }

    public function storeHeldCart(Request $request)
    {
        $v = $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $cart = \App\Models\HeldCart::create([
            'outlet_id' => \App\Models\Outlet::where('active', true)->value('id'),
            'user_id' => auth()->id(),
            'customer_id' => $v['customer_id'] ?? null,
            'label' => ($v['label'] ?? '') !== '' ? $v['label'] : 'Kasir '.now()->format('d M H:i'),
            'items' => collect($v['items'])->map(fn ($i) => [
                'product_id' => $i['product_id'],
                'qty' => $i['qty'],
                'price' => $i['price'],
            ])->values()->all(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'id' => $cart->id]);
        }

        return redirect('/app/pos-hold-orders')->with('success', 'Keranjang ditahan sebagai "'.$cart->label.'".');
    }

    public function storeBroadcast(Request $request)
    {
        $v = $request->validate([
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $query = \App\Models\Customer::where('active', true)->whereNotNull('phone');
        if (! empty($v['customer_group_id'])) {
            $query->where('customer_group_id', $v['customer_group_id']);
        }
        $customers = $query->get();
        if ($customers->isEmpty()) {
            return back()->with('error', 'Tidak ada customer dengan nomor WA pada filter ini.');
        }

        $wa = new \App\Services\WhatsAppService;
        $sent = 0;
        foreach ($customers as $customer) {
            try {
                if ($wa->sendCustom($customer->phone, $v['message'])) {
                    $sent++;
                }
            } catch (\Throwable $e) {
            }
        }

        return back()->with('success', "Broadcast terkirim ke {$sent} dari {$customers->count()} customer.");
    }

    public function storeStockTake(Request $request)
    {
        $v = $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.system_qty' => ['required', 'integer', 'min:0'],
            'items.*.actual_qty' => ['required', 'integer', 'min:0'],
        ]);

        $count = 0;
        \Illuminate\Support\Facades\DB::transaction(function () use ($v, &$count) {
            $prefix = 'SO-'.\Carbon\Carbon::now()->format('Ymd').'-';
            $last = \App\Models\StockOpname::where('opname_number', 'like', $prefix.'%')
                ->orderByRaw('LENGTH(opname_number) DESC, opname_number DESC')->first();
            $num = $last ? (int) substr($last->opname_number, strlen($prefix)) + 1 : 1;

            $opname = \App\Models\StockOpname::create([
                'opname_number' => $prefix.str_pad($num, 4, '0', STR_PAD_LEFT),
                'outlet_id' => $v['outlet_id'],
                'user_id' => auth()->id(),
                'status' => 'completed',
                'notes' => 'Stock take via halaman Tillora pada '.\Carbon\Carbon::now()->format('d/m/Y H:i'),
            ]);

            foreach ($v['items'] as $i) {
                $diff = (int) $i['actual_qty'] - (int) $i['system_qty'];
                $opname->stockOpnameItems()->create([
                    'product_id' => $i['product_id'],
                    'system_stock' => (int) $i['system_qty'],
                    'actual_stock' => (int) $i['actual_qty'],
                    'difference' => $diff,
                    'notes' => 'Stock take Tillora',
                ]);
                if ($diff !== 0) {
                    \App\Models\Product::where('id', $i['product_id'])->update(['current_stock' => (int) $i['actual_qty']]);
                }
                $count++;
            }
        });

        return redirect('/app/inventory-history')->with('success', "Stock take selesai — {$count} produk dicatat.");
    }

    public function profileUpdate(Request $request)
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.auth()->id()],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user = auth()->user();
        $user->name = $v['name'];
        $user->email = $v['email'];
        if (! empty($v['password'])) {
            $user->password = $v['password'];
        }
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function settingsUpdate(Request $request)
    {
        $v = $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'serial_tracking_default' => ['required', 'in:none,optional,required'],
        ]);

        SystemSetting::setValue('app_name', $v['app_name']);
        SystemSetting::setValue('serial_tracking_default', $v['serial_tracking_default']);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
