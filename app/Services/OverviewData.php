<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\SalesTarget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Data asli untuk halaman Store Performance & Business Overview (Tillora).
 */
class OverviewData
{
    public static function storePerformanceWithRequest(\Illuminate\Http\Request $r): array
    {
        return self::storePerformance();
    }

    public static function businessWithRequest(\Illuminate\Http\Request $r): array
    {
        return self::business();
    }

    public static function storePerformance(): array
    {
        $monthStart = Carbon::now()->startOfMonth();
        $curYear = (int) now()->format('Y');
        $curMonth = (int) now()->format('n');
        $outlets = Outlet::where('active', true)->get();

        // Harian 30 hari per outlet, 1 query
        $driver = DB::getDriverName();
        $dateExpr = $driver === 'sqlite' ? "date(created_at)" : "DATE(created_at)";
        $rows = Order::where('order_status', 'completed')
            ->whereDate('created_at', '>=', Carbon::today()->subDays(29))
            ->selectRaw("outlet_id, {$dateExpr} as d, SUM(total_amount) as total, COUNT(*) as cnt")
            ->groupBy('outlet_id', 'd')->get();

        $labels = [];
        for ($i = 29; $i >= 0; $i--) $labels[] = Carbon::today()->subDays($i)->format('d M');
        $byStore = [];
        foreach ($outlets as $o) $byStore[$o->id] = array_fill(0, 30, 0);
        foreach ($rows as $r) {
            $idx = array_search(Carbon::parse($r->d)->format('d M'), $labels);
            if ($idx !== false && isset($byStore[$r->outlet_id])) $byStore[$r->outlet_id][$idx] = (float) $r->total;
        }

        $stores = $outlets->map(function ($o) use ($monthStart, $curYear, $curMonth) {
            $mtd = (float) Order::where('outlet_id', $o->id)->where('created_at', '>=', $monthStart)->where('order_status', 'completed')->sum('total_amount');
            $mcnt = Order::where('outlet_id', $o->id)->where('created_at', '>=', $monthStart)->where('order_status', 'completed')->count();
            $target = (float) SalesTarget::where('outlet_id', $o->id)->where('year', $curYear)->where('month', $curMonth)->value('target_amount') ?: 15000000;
            return [
                'outlet' => $o, 'mtd' => $mtd, 'count' => $mcnt,
                'avg' => $mcnt > 0 ? $mtd / $mcnt : 0,
                'target' => $target, 'pct' => $target > 0 ? $mtd / $target * 100 : 0,
            ];
        })->sortByDesc('mtd')->values();

        $totalMtd = $stores->sum('mtd');
        $avgPct = $stores->count() ? $stores->avg('pct') : 0;
        $best = $stores->first();
        $onTrack = $stores->where('pct', '>=', 80)->count();

        $insights = [];
        if ($best) $insights[] = ['tone' => 'success', 'text' => ($best['outlet']->name ?? 'Toko terbaik').' memimpin dengan '.self::rp($best['mtd']).' bulan ini.'];
        $worst = $stores->last();
        if ($worst && $worst !== $best) $insights[] = ['tone' => 'warning', 'text' => ($worst['outlet']->name ?? 'Satu toko').' baru mencapai '.round($worst['pct']).'% dari target — perlu perhatian.'];
        $peakIdx = 0; $peakVal = 0;
        $totDaily = array_fill(0, 30, 0);
        foreach ($byStore as $s) foreach ($s as $i => $v) $totDaily[$i] += $v;
        foreach ($totDaily as $i => $v) if ($v > $peakVal) { $peakVal = $v; $peakIdx = $i; }
        if ($peakVal > 0) $insights[] = ['tone' => 'info', 'text' => 'Hari tersibuk 30 hari terakhir: '.($labels[$peakIdx] ?? '').' ('.self::rp($peakVal).').'];

        return compact('stores', 'labels', 'byStore', 'totalMtd', 'avgPct', 'best', 'onTrack', 'insights');
    }

    public static function business(): array
    {
        // 12 bulan: revenue, profit, margin
        $mLabels = [];
        $mRev = [];
        $mProfit = [];
        $mMargin = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = Carbon::now()->startOfMonth()->subMonths($i);
            $me = $m->copy()->endOfMonth();
            $r = (float) Order::where('order_status', 'completed')->whereBetween('created_at', [$m, $me])->sum('total_amount');
            $c = (float) OrderItem::whereHas('order', fn ($q) => $q->where('order_status', 'completed')->whereBetween('created_at', [$m, $me]))
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->selectRaw('COALESCE(SUM(order_items.quantity * products.cost_price),0) as c')->value('c');
            $p = max(0, $r - $c);
            $mLabels[] = $m->format('M');
            $mRev[] = $r;
            $mProfit[] = $p;
            $mMargin[] = $r > 0 ? round($p / $r * 100, 1) : 0;
        }

        $yearStart = Carbon::now()->startOfYear();
        $monthIdx = (int) date('n');
        $ytdRev = array_sum(array_slice($mRev, 12 - $monthIdx));
        $ytdProfit = array_sum(array_slice($mProfit, 12 - $monthIdx));
        $ytdMargin = $ytdRev > 0 ? $ytdProfit / $ytdRev * 100 : 0;

        $curYear = (int) date('Y');
        $annualGoal = (float) SalesTarget::where('year', $curYear)->sum('target_amount');
        if (! $annualGoal) $annualGoal = 180000000; // fallback Rp 180jt/thn
        $marginGoal = 40.0;

        $outletCount = Outlet::where('active', true)->count();
        $lowCount = Product::whereColumn('current_stock', '<=', 'min_stock')->count();
        $pendingPay = Order::where('order_status', '!=', 'cancelled')->whereIn('payment_status', ['pending', 'partial'])->count();

        // P&L per kategori (YTD)
        $cats = Category::with([])->get();
        $pnl = [];
        foreach ($cats as $c) {
            $r = (float) OrderItem::whereHas('order', fn ($q) => $q->where('order_status', 'completed')->where('created_at', '>=', $yearStart))
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('products.category_id', $c->id)
                ->selectRaw('COALESCE(SUM(order_items.subtotal),0) as r')->value('r');
            $cost = (float) OrderItem::whereHas('order', fn ($q) => $q->where('order_status', 'completed')->where('created_at', '>=', $yearStart))
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('products.category_id', $c->id)
                ->selectRaw('COALESCE(SUM(order_items.quantity * products.cost_price),0) as c')->value('c');
            if ($r > 0) $pnl[] = ['name' => $c->name, 'rev' => $r, 'cost' => $cost, 'profit' => $r - $cost, 'margin' => round(($r - $cost) / $r * 100, 1)];
        }
        usort($pnl, fn ($a, $b) => $b['rev'] <=> $a['rev']);
        $pnl = array_slice($pnl, 0, 8);

        $risks = [];
        $opps = [];
        if ($lowCount > 0) $risks[] = $lowCount.' produk di bawah stok minimum — risiko lost-sales.';
        if ($pendingPay > 0) $risks[] = $pendingPay.' order menunggu pembayaran — segera tindak lanjuti.';
        if ($ytdMargin < $marginGoal) $risks[] = 'Margin YTD '.number_format($ytdMargin, 1).'% di bawah target '.number_format($marginGoal, 0).'% — evaluasi diskon & pricing.';
        if (! empty($pnl)) $opps[] = 'Kategori terkuat: '.$pnl[0]['name'].' ('.self::rp($pnl[0]['rev']).' YTD) — tambah alokasi display.';
        if ($ytdRev >= $annualGoal * (int) date('n') / 12) $opps[] = 'Pace pendapatan di atas target tahunan — momentum bagus untuk ekspansi.';
        else $opps[] = 'Butuh '.self::rp(max(0, $annualGoal - $ytdRev)).' lagi untuk mencapai target tahunan.';
        if (empty($risks)) $risks[] = 'Tidak ada risiko kritis terdeteksi.';
        if (empty($opps)) $opps[] = 'Belum ada data yang cukup untuk rekomendasi.';

        return compact('mLabels', 'mRev', 'mProfit', 'mMargin', 'ytdRev', 'ytdProfit', 'ytdMargin',
            'annualGoal', 'marginGoal', 'outletCount', 'lowCount', 'pendingPay', 'pnl', 'risks', 'opps');
    }

    public static function rp($n): string
    {
        return 'Rp '.number_format((float) $n, 0, ',', '.');
    }
}
