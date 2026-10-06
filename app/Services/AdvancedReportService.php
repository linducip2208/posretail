<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Sumber data tunggal untuk laporan-laporan baru agar
 * Filament Page, CSV, XLSX, dan scheduled-report konsisten.
 */
class AdvancedReportService
{
    // ---------- 1. PROFIT / HPP per produk ----------

    public static function profitHeaders(): array
    {
        return ['SKU', 'Nama Produk', 'Kategori', 'Qty Terjual', 'Omzet', 'HPP', 'Laba Kotor', 'Margin %', 'Harga Jual', 'Harga Beli'];
    }

    public static function profitNumericColumns(): array
    {
        return ['D', 'E', 'F', 'G', 'H', 'I', 'J'];
    }

    public static function profitRows(string $startDate, string $endDate, ?int $outletId = null): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate.' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('orders.outlet_id', $outletId))
            ->where('orders.order_status', 'completed')
            ->selectRaw('
                COALESCE(products.sku, CONCAT("HAPUS-", order_items.product_id)) as sku,
                COALESCE(products.name, CONCAT("(produk dihapus #", order_items.product_id, ")")) as nama,
                COALESCE(categories.name, "-") as kategori,
                SUM(order_items.quantity) as qty,
                SUM(order_items.subtotal) as omzet,
                SUM(order_items.quantity * COALESCE(products.cost_price, 0)) as hpp,
                COALESCE(MAX(products.selling_price), MAX(order_items.unit_price)) as jual,
                COALESCE(MAX(products.cost_price), 0) as beli
            ')
            ->groupBy('order_items.product_id', 'products.sku', 'products.name', 'categories.name')
            ->orderByDesc('omzet')
            ->get();

        return $rows->map(function ($r) {
            $laba = (float) $r->omzet - (float) $r->hpp;
            $margin = (float) $r->omzet > 0 ? round($laba / (float) $r->omzet * 100, 1) : 0;

            return [
                $r->sku, $r->nama, $r->kategori,
                (int) $r->qty, (float) $r->omzet, (float) $r->hpp,
                (float) $laba, (float) $margin, (float) $r->jual, (float) $r->beli,
            ];
        })->toArray();
    }

    // ---------- 2. SLOW / DEAD STOCK ----------

    public static function slowHeaders(): array
    {
        return ['SKU', 'Nama Produk', 'Stok', 'Nilai Stok (HPP)', 'Terjual 30hr', 'Terakhir Laku', 'Hari Mati', 'Status'];
    }

    public static function slowNumericColumns(): array
    {
        return ['C', 'D', 'E', 'G'];
    }

    public static function slowRows(?int $outletId = null, int $periodDays = 30): array
    {
        $since = now()->subDays($periodDays)->format('Y-m-d H:i:s');

        $sold = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'completed')
            ->where('orders.created_at', '>=', $since)
            ->when($outletId, fn ($q) => $q->where('orders.outlet_id', $outletId))
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as qty_30, MAX(orders.created_at) as last_sold')
            ->groupBy('order_items.product_id');

        $products = DB::table('products')
            ->leftJoinSub($sold, 's', 's.product_id', '=', 'products.id')
            ->when($outletId, fn ($q) => $q->where('products.outlet_id', $outletId))
            ->where('products.active', true)
            ->selectRaw('products.sku, products.name, products.current_stock, products.cost_price,
                COALESCE(s.qty_30, 0) as qty_30, s.last_sold')
            ->orderBy('qty_30')
            ->orderByDesc('products.current_stock')
            ->limit(500)
            ->get();

        return $products->map(function ($p) {
            $lastSold = $p->last_sold ? \Carbon\Carbon::parse($p->last_sold) : null;
            $deadDays = $lastSold ? (int) $lastSold->diffInDays(now()) : 999;
            $status = $deadDays >= 90 || $p->qty_30 == 0 && $deadDays >= 60 ? 'Mati (Dead)'
                : ($deadDays >= 30 || $p->qty_30 <= 2 ? 'Lambat (Slow)' : 'Normal');

            return [
                $p->sku, $p->name,
                (int) $p->current_stock,
                (float) ((int) $p->current_stock * (float) $p->cost_price),
                (int) $p->qty_30,
                $lastSold ? $lastSold->format('Y-m-d') : '-',
                (int) $deadDays,
                $status,
            ];
        })->toArray();
    }

    // ---------- 3. ARUS KAS HARIAN ----------

    public static function cashflowHeaders(): array
    {
        return ['Tanggal', 'Kas Masuk (Penjualan)', 'Kas Keluar (Beban)', 'Arus Bersih', 'Jml Transaksi'];
    }

    public static function cashflowNumericColumns(): array
    {
        return ['B', 'C', 'D', 'E'];
    }

    public static function cashflowRows(string $startDate, string $endDate, ?int $outletId = null): array
    {
        $in = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->whereBetween('payments.created_at', [$startDate, $endDate.' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('orders.outlet_id', $outletId))
            ->whereIn('payments.status', ['success', 'confirmed', 'completed'])
            ->where('orders.order_status', '!=', 'cancelled')
            ->selectRaw('DATE(payments.created_at) as tgl, SUM(payments.amount) as masuk, COUNT(*) as trx')
            ->groupBy('tgl')
            ->pluck('masuk', 'tgl');

        $inCount = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->whereBetween('payments.created_at', [$startDate, $endDate.' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('orders.outlet_id', $outletId))
            ->whereIn('payments.status', ['success', 'confirmed', 'completed'])
            ->where('orders.order_status', '!=', 'cancelled')
            ->selectRaw('DATE(payments.created_at) as tgl, COUNT(*) as trx')
            ->groupBy('tgl')
            ->pluck('trx', 'tgl');

        $out = DB::table('expenses')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->selectRaw('DATE(expense_date) as tgl, SUM(amount) as keluar')
            ->groupBy('tgl')
            ->pluck('keluar', 'tgl');

        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            (new \DateTime($endDate))->modify('+1 day')
        );

        $rows = [];
        foreach ($period as $d) {
            $t = $d->format('Y-m-d');
            $masuk = (float) ($in[$t] ?? 0);
            $keluar = (float) ($out[$t] ?? 0);
            $rows[] = [$t, $masuk, $keluar, $masuk - $keluar, (int) ($inCount[$t] ?? 0)];
        }

        return $rows;
    }

    // ---------- 4. HUTANG SUPPLIER ----------

    public static function payableHeaders(): array
    {
        return ['Supplier', 'No. Invoice', 'Total', 'Terbayar', 'Sisa', 'Jatuh Tempo', 'Overdue (hari)', 'Status'];
    }

    public static function payableNumericColumns(): array
    {
        return ['C', 'D', 'E', 'G'];
    }

    public static function payableRows(?int $outletId = null): array
    {
        $q = DB::table('supplier_payables')
            ->leftJoin('suppliers', 'supplier_payables.supplier_id', '=', 'suppliers.id')
            ->leftJoin('purchase_orders', 'supplier_payables.purchase_order_id', '=', 'purchase_orders.id')
            ->selectRaw('COALESCE(suppliers.name, "-") as supplier, supplier_payables.invoice_number,
                supplier_payables.total_amount, COALESCE(supplier_payables.paid_amount,0) as paid,
                supplier_payables.due_date, supplier_payables.status, purchase_orders.outlet_id')
            ->orderBy('supplier_payables.due_date');

        if ($outletId) {
            $q->where('purchase_orders.outlet_id', $outletId);
        }

        return $q->get()->map(function ($r) {
            $sisa = (float) $r->total_amount - (float) $r->paid;
            $due = $r->due_date ? \Carbon\Carbon::parse($r->due_date) : null;
            $overdue = $due && $due->isPast() && $sisa > 0 ? (int) $due->diffInDays(now()) : 0;
            $status = $sisa <= 0 ? 'Lunas' : ($overdue > 0 ? 'Jatuh Tempo' : ($r->status ?? 'pending'));

            return [
                $r->supplier, $r->invoice_number,
                (float) $r->total_amount, (float) $r->paid, (float) $sisa,
                $due ? $due->format('Y-m-d') : '-', (int) $overdue, (string) $status,
            ];
        })->toArray();
    }

    // ---------- 5. PAJAK ----------

    public static function taxHeaders(): array
    {
        return ['No. Faktur', 'Tanggal', 'Pelanggan', 'DPP', 'PPN', 'Total', 'Status'];
    }

    public static function taxNumericColumns(): array
    {
        return ['D', 'E', 'F'];
    }

    public static function taxRows(string $startDate, string $endDate, ?int $outletId = null): array
    {
        return DB::table('tax_invoices')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->orderBy('invoice_date')
            ->get()
            ->map(fn ($t) => [
                $t->invoice_number,
                \Carbon\Carbon::parse($t->invoice_date)->format('Y-m-d'),
                $t->customer_name ?? '-',
                (float) $t->dpp, (float) $t->ppn_amount, (float) $t->total_amount,
                $t->status ?? '-',
            ])->toArray();
    }

    // ---------- 6. PELANGGAN RFM ----------

    public static function rfmHeaders(): array
    {
        return ['Pelanggan', 'HP', 'Terakhir Belanja', 'Recency (hari)', 'Frekuensi', 'Monetary', 'Segmen'];
    }

    public static function rfmNumericColumns(): array
    {
        return ['D', 'E', 'F'];
    }

    public static function rfmRows(string $startDate, string $endDate, ?int $outletId = null, int $limit = 300): array
    {
        $rows = DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate.' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('orders.outlet_id', $outletId))
            ->where('orders.order_status', 'completed')
            ->whereNotNull('orders.customer_id')
            ->selectRaw('customers.name as nama, customers.phone as hp,
                MAX(orders.created_at) as last_at, COUNT(*) as freq, SUM(orders.total_amount) as money')
            ->groupBy('orders.customer_id', 'customers.name', 'customers.phone')
            ->orderByDesc('money')
            ->limit($limit)
            ->get();

        return $rows->map(function ($r) {
            $recency = $r->last_at ? (int) \Carbon\Carbon::parse($r->last_at)->diffInDays(now()) : 999;
            $segmen = match (true) {
                $recency <= 14 && $r->freq >= 5 => 'Champions',
                $recency <= 30 && $r->freq >= 3 => 'Loyal',
                $recency <= 60 => 'Aktif',
                $recency <= 120 => 'Hampir Hilang',
                default => 'Churn / Perlu Reactivasi',
            };

            return [
                $r->nama ?? '(tanpa nama)', $r->hp ?? '-',
                $r->last_at ? \Carbon\Carbon::parse($r->last_at)->format('Y-m-d') : '-',
                (int) $recency, (int) $r->freq, (float) $r->money, $segmen,
            ];
        })->toArray();
    }

    // ---------- 7. EFEKTIVITAS DISKON ----------

    public static function promoHeaders(): array
    {
        return ['Bucket Diskon', 'Jml Transaksi', 'Omzet Kotor', 'Total Diskon', 'Omzet Bersih', 'Diskon %', 'Rata2/Trx'];
    }

    public static function promoNumericColumns(): array
    {
        return ['B', 'C', 'D', 'E', 'F', 'G'];
    }

    public static function promoRows(string $startDate, string $endDate, ?int $outletId = null): array
    {
        $orders = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->where('order_status', 'completed')
            ->select(['subtotal', 'discount_amount', 'total_amount'])
            ->get();

        $buckets = [
            'Tanpa Diskon' => ['min' => -1, 'max' => 0],
            'Diskon Kecil (<5%)' => ['min' => 0, 'max' => 5],
            'Diskon Sedang (5-10%)' => ['min' => 5, 'max' => 10],
            'Diskon Besar (>10%)' => ['min' => 10, 'max' => 1000],
        ];

        $agg = [];
        foreach ($buckets as $label => $b) {
            $agg[$label] = ['trx' => 0, 'gross' => 0, 'disc' => 0, 'net' => 0];
        }

        foreach ($orders as $o) {
            $pct = (float) $o->subtotal > 0 ? ((float) $o->discount_amount / (float) $o->subtotal * 100) : 0;
            $label = 'Tanpa Diskon';
            if ($pct > 10) {
                $label = 'Diskon Besar (>10%)';
            } elseif ($pct >= 5) {
                $label = 'Diskon Sedang (5-10%)';
            } elseif ($pct > 0) {
                $label = 'Diskon Kecil (<5%)';
            }
            $agg[$label]['trx']++;
            $agg[$label]['gross'] += (float) $o->subtotal;
            $agg[$label]['disc'] += (float) $o->discount_amount;
            $agg[$label]['net'] += (float) $o->total_amount;
        }

        $out = [];
        foreach ($agg as $label => $a) {
            $discPct = $a['gross'] > 0 ? round($a['disc'] / $a['gross'] * 100, 1) : 0;
            $avg = $a['trx'] > 0 ? round($a['net'] / $a['trx']) : 0;
            $out[] = [$label, (int) $a['trx'], (float) $a['gross'], (float) $a['disc'], (float) $a['net'], (float) $discPct, (float) $avg];
        }

        return $out;
    }

    // ---------- Ringkasan harian (untuk WA/Email terjadwal) ----------

    public static function dailySummary(?int $outletId = null, ?string $date = null): array
    {
        $date ??= now()->format('Y-m-d');

        $sales = DB::table('orders')
            ->whereDate('created_at', $date)
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->where('order_status', 'completed')
            ->selectRaw('COUNT(*) as trx, COALESCE(SUM(total_amount),0) as omzet, COALESCE(SUM(discount_amount),0) as diskon')
            ->first();

        $top = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->whereDate('orders.created_at', $date)
            ->when($outletId, fn ($q) => $q->where('orders.outlet_id', $outletId))
            ->where('orders.order_status', 'completed')
            ->selectRaw('COALESCE(products.name, "Item") as nama, SUM(order_items.quantity) as qty')
            ->groupBy('order_items.product_id', 'products.name')
            ->orderByDesc('qty')
            ->limit(3)
            ->get();

        $overduePayables = (int) DB::table('supplier_payables')
            ->where('status', 'pending')->where('due_date', '<', now())->count();

        $lowStock = (int) DB::table('products')
            ->where('active', true)
            ->where('min_stock', '>', 0)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->count();

        return [
            'date' => $date,
            'trx' => (int) ($sales->trx ?? 0),
            'omzet' => (float) ($sales->omzet ?? 0),
            'diskon' => (float) ($sales->diskon ?? 0),
            'top' => $top,
            'overduePayables' => $overduePayables,
            'lowStock' => $lowStock,
        ];
    }
}
