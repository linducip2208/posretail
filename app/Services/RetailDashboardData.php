<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\SalesTarget;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Sumber data tunggal untuk dashboard gaya Tillora.
 * Dipakai oleh TilloraDashboardController (/tillora)
 * dan TilloraDashboardWidget (dashboard default /admin).
 */
class RetailDashboardData
{
    public static function get(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $user = auth()->user();
        $hour = (int) now()->format('H');
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));

        // --- KPI hari ini ---
        $todayOrdersQ = Order::whereDate('created_at', $today)->where('order_status', 'completed');
        $todayRevenue = (float) (clone $todayOrdersQ)->sum('total_amount');
        $todayCount = (clone $todayOrdersQ)->count();
        $todayItems = OrderItem::whereHas('order', fn ($q) => $q->whereDate('created_at', $today)->where('order_status', 'completed'))->sum('quantity');

        $yRevenue = (float) Order::whereDate('created_at', $yesterday)->where('order_status', 'completed')->sum('total_amount');
        $yCount = Order::whereDate('created_at', $yesterday)->where('order_status', 'completed')->count();

        $avgBasket = $todayCount > 0 ? $todayRevenue / $todayCount : 0;
        $itemsPerBasket = $todayCount > 0 ? $todayItems / $todayCount : 0;

        $pctRev = $yRevenue > 0 ? (($todayRevenue - $yRevenue) / $yRevenue * 100) : ($todayRevenue > 0 ? 100 : 0);
        $pctCount = $yCount > 0 ? (($todayCount - $yCount) / $yCount * 100) : ($todayCount > 0 ? 100 : 0);

        // Gross margin: (revenue - COGS) / revenue
        $cogs = (float) OrderItem::whereHas('order', fn ($q) => $q->whereDate('created_at', $today)->where('order_status', 'completed'))
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('COALESCE(SUM(order_items.quantity * products.cost_price),0) as cogs')
            ->value('cogs');
        $margin = $todayRevenue > 0 ? (($todayRevenue - $cogs) / $todayRevenue * 100) : 0;

        // --- Revenue trend 30 hari ---
        $labels = [];
        $thisPeriod = [];
        $prevPeriod = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $labels[] = $d->format('d M');
            $thisPeriod[] = (float) Order::whereDate('created_at', $d)->where('order_status', 'completed')->sum('total_amount');
            $pd = $d->copy()->subDays(30);
            $prevPeriod[] = (float) Order::whereDate('created_at', $pd)->where('order_status', 'completed')->sum('total_amount');
        }
        $sparkRev = array_slice($thisPeriod, -10);

        // --- Margin harian 10 hari terakhir (data asli per hari) ---
        $revByDay = Order::where('created_at', '>=', Carbon::today()->subDays(9))
            ->where('order_status', 'completed')
            ->selectRaw('DATE(created_at) as d, SUM(total_amount) as t')
            ->groupBy('d')->pluck('t', 'd');
        $cogsByDay = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.created_at', '>=', Carbon::today()->subDays(9))
            ->where('orders.order_status', 'completed')
            ->selectRaw('DATE(orders.created_at) as d, SUM(order_items.quantity * products.cost_price) as c')
            ->groupBy('d')->pluck('c', 'd');
        $marginSpark = [];
        for ($i = 9; $i >= 0; $i--) {
            $key = Carbon::today()->subDays($i)->toDateString();
            $r = (float) ($revByDay[$key] ?? 0);
            $c = (float) ($cogsByDay[$key] ?? 0);
            $marginSpark[] = $r > 0 ? round(($r - $c) / $r * 100, 1) : 0;
        }

        // --- Sales by outlet (donut) ---
        $byOutlet = Order::whereDate('created_at', $today)->where('order_status', 'completed')
            ->selectRaw('outlet_id, SUM(total_amount) as total')
            ->groupBy('outlet_id')->with('outlet')->orderByDesc('total')->get();
        $outletNames = $byOutlet->map(fn ($r) => $r->outlet?->name ?? ('Outlet #' . $r->outlet_id))->values();
        $outletTotals = $byOutlet->map(fn ($r) => (float) $r->total)->values();

        // --- Transaksi terbaru ---
        $recent = Order::with(['customer', 'outlet'])
            ->withCount('orderItems')
            ->latest()->limit(12)->get();

        // --- Low stock ---
        $lowStock = Product::whereColumn('current_stock', '<=', 'min_stock')
            ->orderBy('current_stock')->limit(6)->get();
        $outOfStock = (clone $lowStock)->where('current_stock', '<=', 0)->count();
        $criticalCount = Product::where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'min_stock')->count();

        // --- Top products 7 hari ---
        $topProducts = OrderItem::whereHas('order', fn ($q) => $q->where('created_at', '>=', Carbon::now()->subDays(7))->where('order_status', 'completed'))
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')->orderByDesc('qty')->limit(5)->with('product')->get();
        $maxQty = $topProducts->max('qty') ?: 1;

        // --- Sales by hour hari ini ---
        $hourly = array_fill(0, 24, 0);
        $driver = DB::getDriverName();
        try {
            if ($driver === 'sqlite') {
                $rows = Order::whereDate('created_at', $today)->where('order_status', 'completed')
                    ->selectRaw('strftime("%H", created_at) as h, SUM(total_amount) as total')
                    ->groupBy('h')->get();
            } else {
                $rows = Order::whereDate('created_at', $today)->where('order_status', 'completed')
                    ->selectRaw('HOUR(created_at) as h, SUM(total_amount) as total')
                    ->groupBy('h')->get();
            }
            foreach ($rows as $r) {
                $h = (int) $r->h;
                if ($h >= 0 && $h < 24) $hourly[$h] = (float) $r->total;
            }
        } catch (\Throwable $e) {
        }
        $hourLabels = [];
        $hourData = [];
        for ($h = 8; $h <= 21; $h++) {
            $hourLabels[] = $h . ':00';
            $hourData[] = $hourly[$h] ?? 0;
        }
        $peakHour = array_search(max($hourData), $hourData);
        $peakLabel = $hourLabels[$peakHour] ?? '-';

        // --- Team on shift ---
        $shifts = Shift::where('status', 'open')->with(['user', 'outlet'])->latest()->limit(6)->get();
        if ($shifts->isEmpty()) {
            $shifts = User::where('active', true)->limit(5)->get()->map(fn ($u) => (object) ['user' => $u, 'outlet' => null, 'status' => 'standby']);
        }

        // --- Store performance MTD ---
        $outlets = Outlet::where('active', true)->get();
        $monthStart = Carbon::now()->startOfMonth();
        $curYear = (int) now()->format('Y');
        $curMonth = (int) now()->format('n');
        $storePerf = $outlets->map(function ($o) use ($monthStart, $curYear, $curMonth) {
            $mtd = (float) Order::where('outlet_id', $o->id)->where('created_at', '>=', $monthStart)->where('order_status', 'completed')->sum('total_amount');
            $target = (float) SalesTarget::where('outlet_id', $o->id)
                ->where('year', $curYear)->where('month', $curMonth)
                ->value('target_amount') ?: 0;
            if (! $target) $target = 15000000;
            $pct = $target > 0 ? min(120, $mtd / $target * 100) : 0;

            return (object) ['outlet' => $o, 'mtd' => $mtd, 'target' => $target, 'pct' => $pct];
        });

        $customersToday = Order::whereDate('created_at', $today)->distinct('customer_id')->count('customer_id');        $monthlyTarget = (float) SalesTarget::where('year', $curYear)->where('month', $curMonth)->sum('target_amount');
        if (! $monthlyTarget) $monthlyTarget = 18000000 * max(1, $outlets->count());
        $daysInMonth = (int) now()->daysInMonth;
        $dailyTarget = $daysInMonth > 0 ? $monthlyTarget / $daysInMonth : $monthlyTarget;
        $dailyPct = $dailyTarget > 0 ? min(100, $todayRevenue / $dailyTarget * 100) : 0;

        return compact(
            'user', 'greeting', 'today', 'todayRevenue', 'todayCount', 'avgBasket', 'itemsPerBasket',
            'pctRev', 'pctCount', 'margin', 'labels', 'thisPeriod', 'prevPeriod', 'sparkRev',
            'byOutlet', 'outletNames', 'outletTotals', 'recent', 'lowStock', 'outOfStock', 'criticalCount',
            'topProducts', 'maxQty', 'hourLabels', 'hourData', 'peakLabel', 'shifts', 'marginSpark',
            'storePerf', 'customersToday', 'dailyTarget', 'dailyPct', 'yRevenue'
        );
    }
}
