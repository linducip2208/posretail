<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Data untuk halaman pemilih (ketika halaman detail dibuka tanpa ?id=).
 */
class DetailPickerData
{
    public static function for(string $page): array
    {
        return match ($page) {
            'sale-details' => [
                'title' => 'Pilih Transaksi',
                'subtitle' => 'Cari nomor struk atau pilih dari penjualan terbaru.',
                'searchAction' => '/app/sales-list',
                'placeholder' => 'Cari nomor struk / pelanggan…',
                'items' => Order::with('customer')->latest()->limit(12)->get()->map(fn ($o) => [
                    'url' => '/app/sale-details?id='.$o->order_number,
                    'title' => $o->order_number,
                    'sub' => ($o->customer?->name ?? 'Walk-in').' · Rp '.number_format((float) $o->total_amount, 0, ',', '.'),
                ])->all(),
            ],
            'product-details' => [
                'title' => 'Pilih Produk',
                'subtitle' => 'Cari nama/SKU atau pilih dari katalog terbaru.',
                'searchAction' => '/app/products-list',
                'placeholder' => 'Cari nama produk / SKU…',
                'items' => Product::latest()->limit(12)->get()->map(fn ($p) => [
                    'url' => '/app/product-details?id='.$p->id,
                    'title' => $p->name,
                    'sub' => ($p->sku ?? '').' · Rp '.number_format((float) $p->selling_price, 0, ',', '.'),
                ])->all(),
            ],
            'customer-details' => [
                'title' => 'Pilih Pelanggan',
                'subtitle' => 'Cari nama/telepon atau pilih dari pelanggan terbaru.',
                'searchAction' => '/app/customers-list',
                'placeholder' => 'Cari nama / telepon…',
                'items' => Customer::latest()->limit(12)->get()->map(fn ($c) => [
                    'url' => '/app/customer-details?id='.$c->id,
                    'title' => $c->name,
                    'sub' => ($c->phone ?? '-').' · '.($c->email ?? ''),
                ])->all(),
            ],
            'employee-details' => [
                'title' => 'Pilih Karyawan',
                'subtitle' => 'Pilih dari daftar tim aktif.',
                'searchAction' => '/app/employees-list',
                'placeholder' => 'Cari nama karyawan…',
                'items' => User::where('active', true)->orderBy('name')->limit(20)->get()->map(fn ($u) => [
                    'url' => '/app/employee-details?id='.$u->id,
                    'title' => $u->name,
                    'sub' => ucfirst($u->role ?? 'staff').' · '.($u->email ?? ''),
                ])->all(),
            ],
            'purchase-order-details' => [
                'title' => 'Pilih Purchase Order',
                'subtitle' => 'Cari nomor PO atau pilih dari PO terbaru.',
                'searchAction' => '/app/purchase-orders',
                'placeholder' => 'Cari nomor PO / supplier…',
                'items' => PurchaseOrder::with('supplier')->latest()->limit(12)->get()->map(fn ($o) => [
                    'url' => '/app/purchase-order-details?id='.$o->id,
                    'title' => $o->po_number ?? ('PO #'.$o->id),
                    'sub' => ($o->supplier?->name ?? '-').' · '.ucfirst($o->status ?? '-'),
                ])->all(),
            ],
            'supplier-details' => [
                'title' => 'Pilih Supplier',
                'subtitle' => 'Cari nama supplier atau pilih dari direktori.',
                'searchAction' => '/app/suppliers-list',
                'placeholder' => 'Cari supplier…',
                'items' => Supplier::orderBy('name')->limit(20)->get()->map(fn ($s) => [
                    'url' => '/app/supplier-details?id='.$s->id,
                    'title' => $s->name,
                    'sub' => $s->phone ?? ($s->email ?? ''),
                ])->all(),
            ],
            default => [
                'title' => 'Pilih Data',
                'subtitle' => 'Pilih dari daftar.',
                'searchAction' => '/app',
                'placeholder' => 'Cari…',
                'items' => [],
            ],
        };
    }
}
