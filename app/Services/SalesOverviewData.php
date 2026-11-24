<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Retur;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Data halaman Sales Overview (Tillora) — selalu data asli.
 * Range: 7d | 30d | 90d (default, mencakup data aktual) | 12m.
 */
class SalesOverviewData
{
    public static function ranges(): array
    {
        return ['7d' => 7, '30d' => 30, '90d' => 90, '12m' => 365];
    }

    public static function getWithRequest(\Illuminate\Http\Request $r): array
    {
        return self::get($r->input('range', '90d'));
    }

    public static function get(string $range = '90d'): array
    {
        $days = self::ranges()[$range] ?? 90;
        $end = Carbon::today();
        $start = Carbon::today()->subDays($days - 1);
        $prevEnd = $start->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays($days - 1);
        $rangeLabel = match ($range) {
            '7d' => 'last 7 days',
            '30d' => 'last 30 days',
            '12m' => 'last 12 months',
            default => 'last 90 days',
        };

        $q = fn ($s, $e) => Order::where('order_status', 'completed')
            ->whereDate('created_at', '>=', $s)->whereDate('created_at', '<=', $e);

        $rev = (float) (clone $q($start, $end))->sum('total_amount');
        $cnt = (clone $q($start, $end))->count();
        $prevRev = (float) (clone $q($prevStart, $prevEnd))->sum('total_amount');
        $prevCnt = (clone $q($prevStart, $prevEnd))->count();

        $aov = $cnt > 0 ? $rev / $cnt : 0;
        $prevAov = $prevCnt > 0 ? $prevRev / $prevCnt : 0;

        $retCount = Retur::whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->count();
        $prevRet = Retur::whereDate('created_at', '>=', $prevStart)->whereDate('created_at', '<=', $prevEnd)->count();
        $retRate = $cnt > 0 ? $retCount / $cnt * 100 : 0;
        $prevRetRate = $prevCnt > 0 ? $prevRet / $prevCnt * 100 : 0;

        $pct = fn ($a, $b) => $b > 0 ? ($a - $b) / $b * 100 : ($a > 0 ? 100 : 0);

        // --- Harian: revenue + gross profit ---
        $labels = [];
        $revDaily = [];
        $profitDaily = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            // batasi titik chart 12m agar ringan: agregat mingguan
            $labels[] = $d->format('d M');
            $r = (float) Order::where('order_status', 'completed')->whereDate('created_at', $d)->sum('total_amount');
            $cogs = (float) OrderItem::whereHas('order', fn ($qq) => $qq->where('order_status', 'completed')->whereDate('created_at', $d))
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->selectRaw('COALESCE(SUM(order_items.quantity * products.cost_price),0) as c')->value('c');
            $revDaily[] = $r;
            $profitDaily[] = max(0, $r - $cogs);
        }
        if ($range === '12m') {
            // downsample mingguan -> ~52 titik
            $labels = array_values(array_filter($labels, fn ($_, $i) => $i % 7 === 0, ARRAY_FILTER_USE_BOTH));
            $revDaily = array_values(array_filter($revDaily, fn ($_, $i) => $i % 7 === 0, ARRAY_FILTER_USE_BOTH));
            $profitDaily = array_values(array_filter($profitDaily, fn ($_, $i) => $i % 7 === 0, ARRAY_FILTER_USE_BOTH));
        }

        // --- Metode pembayaran ---
        $payRows = Payment::whereHas('order', fn ($qq) => $qq->where('order_status', 'completed')
                ->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end))
            ->selectRaw('payment_method_id, SUM(amount) as total, COUNT(*) as n')
            ->groupBy('payment_method_id')->with('paymentMethod')->orderByDesc('total')->get();
        $payNames = $payRows->map(fn ($r) => $r->paymentMethod?->name ?? ('Metode #' . $r->payment_method_id))->values();
        $payTotals = $payRows->map(fn ($r) => (float) $r->total)->values();

        // --- Kategori ---
        $catRows = OrderItem::whereHas('order', fn ($qq) => $qq->where('order_status', 'completed')
                ->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end))
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw('COALESCE(categories.name, ?) as name, SUM(order_items.subtotal) as total', ['Lainnya'])
            ->groupBy('name')->orderByDesc('total')->limit(8)->get();
        $catNames = $catRows->pluck('name')->values();
        $catTotals = $catRows->map(fn ($r) => (float) $r->total)->values();

        // --- Basket 12 bulan ---
        $basketLabels = [];
        $basketValue = [];
        $basketItems = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = Carbon::now()->startOfMonth()->subMonths($i);
            $me = $m->copy()->endOfMonth();
            $mr = (float) Order::where('order_status', 'completed')->whereBetween('created_at', [$m, $me])->sum('total_amount');
            $mc = Order::where('order_status', 'completed')->whereBetween('created_at', [$m, $me])->count();
            $mi = OrderItem::whereHas('order', fn ($qq) => $qq->where('order_status', 'completed')->whereBetween('created_at', [$m, $me]))->sum('quantity');
            $basketLabels[] = $m->format('M');
            $basketValue[] = $mc > 0 ? round($mr / $mc, 2) : 0;
            $basketItems[] = $mc > 0 ? round($mi / $mc, 2) : 0;
        }

        // --- Heatmap jam x hari (Senin dulu) ---
        $driver = DB::getDriverName();
        $heat = array_fill(0, 7, array_fill(0, 12, 0)); // 7 hari x jam 9-20
        try {
            if ($driver === 'sqlite') {
                $rows = Order::where('order_status', 'completed')
                    ->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)
                    ->selectRaw('strftime("%w", created_at) as wd, strftime("%H", created_at) as h, SUM(total_amount) as total')
                    ->groupBy('wd', 'h')->get();
                foreach ($rows as $r) {
                    $di = ((int) $r->wd + 6) % 7; // Sen=0
                    $h = (int) $r->h;
                    if ($h >= 9 && $h <= 20) $heat[$di][$h - 9] += (float) $r->total;
                }
            } else {
                $rows = Order::where('order_status', 'completed')
                    ->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)
                    ->selectRaw('DAYOFWEEK(created_at) as wd, HOUR(created_at) as h, SUM(total_amount) as total')
                    ->groupBy('wd', 'h')->get();
                foreach ($rows as $r) {
                    $di = ((int) $r->wd + 5) % 7; // DAYOFWEEK Sen=2 -> 0
                    $h = (int) $r->h;
                    if ($h >= 9 && $h <= 20) $heat[$di][$h - 9] += (float) $r->total;
                }
            }
        } catch (\Throwable $e) {
        }
        $heatMax = 1;
        foreach ($heat as $row) foreach ($row as $v) $heatMax = max($heatMax, $v);

        // --- Tim ---
        $team = Order::where('order_status', 'completed')
            ->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)
            ->selectRaw('user_id, COUNT(*) as cnt, SUM(total_amount) as total')
            ->groupBy('user_id')->with('user')->orderByDesc('total')->limit(10)->get();
        $topTeam = (float) ($team->max('total') ?: 1);

        return compact('range', 'rangeLabel', 'days', 'start', 'end', 'rev', 'cnt', 'aov',
            'prevRev', 'prevCnt', 'prevAov', 'retCount', 'retRate', 'prevRetRate',
            'labels', 'revDaily', 'profitDaily', 'payNames', 'payTotals', 'catNames', 'catTotals',
            'basketLabels', 'basketValue', 'basketItems', 'heat', 'heatMax', 'team', 'topTeam')
            + ['pctRev' => $pct($rev, $prevRev), 'pctCnt' => $pct($cnt, $prevCnt),
               'pctAov' => $pct($aov, $prevAov), 'retDiff' => $retRate - $prevRetRate];
    }
}
