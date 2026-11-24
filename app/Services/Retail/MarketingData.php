<?php

namespace App\Services\Retail;

use App\Models\Bundle;
use App\Models\Category;
use App\Models\DiscountTemplate;
use App\Models\GiftCard;
use App\Models\GiftCardUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Retur;
use App\Models\Shift;
use App\Models\SystemSetting;
use App\Models\VolumePricing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** DATA ASLI halaman Marketing + Reports + Settings + Profile. */
class MarketingData
{
    /** Order yang batal tidak dihitung sebagai penjualan. */
    private static function liveOrders()
    {
        return Order::where('order_status', '!=', 'cancelled');
    }

    /** Peta tanggal => nilai untuk 30 hari terakhir (diisi 0 bila kosong). */
    private static function last30Days(): array
    {
        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i)->toDateString();
            $days[$d] = 0;
        }

        return $days;
    }

    public static function coupons(Request $r): array
    {
        $cards = GiftCard::with(['customer', 'usages'])->orderByDesc('id')->get();
        $today = Carbon::today();

        $active = $cards->filter(fn ($c) => $c->status === 'active'
            && ($c->used_count < ($c->max_usage ?? 1))
            && (!$c->valid_until || Carbon::parse($c->valid_until)->gte($today)));

        $expired = $cards->filter(fn ($c) => $c->status === 'expired'
            || ($c->valid_until && Carbon::parse($c->valid_until)->lt($today)));

        $usedTotal = (int) $cards->sum('used_count');

        $daily = self::last30Days();
        $usage = GiftCardUsage::where('created_at', '>=', Carbon::today()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')->pluck('c', 'd');
        foreach ($usage as $d => $c) {
            if (array_key_exists($d, $daily)) {
                $daily[$d] = (int) $c;
            }
        }

        return [
            'cards' => $cards,
            'featured' => $cards->take(3),
            'totalCards' => $cards->count(),
            'activeCards' => $active->count(),
            'usedTotal' => $usedTotal,
            'expiredCards' => $expired->count(),
            'usageLabels' => array_keys($daily),
            'usageData' => array_values($daily),
        ];
    }

    public static function discounts(Request $r): array
    {
        $templates = DiscountTemplate::with('outlet')->orderByDesc('id')->get();
        $volumes = VolumePricing::with(['product', 'customerGroup'])->orderBy('min_qty')->get();

        $discount30d = (float) (clone self::liveOrders())
            ->where('created_at', '>=', Carbon::today()->subDays(29)->startOfDay())
            ->sum('discount_amount');

        return [
            'templates' => $templates,
            'activeTemplates' => $templates->where('active', true)->count(),
            'totalTemplates' => $templates->count(),
            'volumes' => $volumes,
            'activeVolumes' => $volumes->where('active', true)->count(),
            'discount30d' => $discount30d,
        ];
    }

    public static function promotions(Request $r): array
    {
        $bundles = Bundle::with(['items.product', 'outlet'])->withCount('items')
            ->orderByDesc('id')->get()
            ->map(function ($b) {
                $normal = (float) $b->items->sum(fn ($it) => (float) ($it->product->selling_price ?? 0) * (int) $it->quantity);
                $b->normal_total = $normal;
                $b->saving = max(0, $normal - (float) $b->bundle_price);
                $b->saving_pct = $normal > 0 ? round($b->saving / $normal * 100, 1) : 0;

                return $b;
            });

        $giftPromos = GiftCard::with('customer')->where('status', 'active')
            ->orderByDesc('id')->get();

        return [
            'bundles' => $bundles,
            'bundleCount' => $bundles->count(),
            'activeBundles' => $bundles->where('active', true)->count(),
            'giftPromos' => $giftPromos,
            'giftActiveCount' => $giftPromos->count(),
        ];
    }

    public static function reportsOverview(Request $r): array
    {
        $start = Carbon::today()->subDays(29)->startOfDay();
        $prevStart = Carbon::today()->subDays(59)->startOfDay();
        $prevEnd = Carbon::today()->subDays(30)->endOfDay();

        $rev30 = (float) (clone self::liveOrders())->where('created_at', '>=', $start)->sum('total_amount');
        $cnt30 = (clone self::liveOrders())->where('created_at', '>=', $start)->count();
        $revPrev = (float) (clone self::liveOrders())->whereBetween('created_at', [$prevStart, $prevEnd])->sum('total_amount');
        $cntPrev = (clone self::liveOrders())->whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $cogs30 = (float) OrderItem::whereHas('order', fn ($q) => $q->where('order_status', '!=', 'cancelled')->where('created_at', '>=', $start))
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('COALESCE(SUM(order_items.quantity * products.cost_price),0) as c')->value('c');
        $profit30 = $rev30 - $cogs30;
        $margin30 = $rev30 > 0 ? round($profit30 / $rev30 * 100, 1) : 0;

        $retCount = Retur::where('created_at', '>=', $start)->count();
        $retRate = $cnt30 > 0 ? round($retCount / $cnt30 * 100, 1) : 0;

        $daily = self::last30Days();
        $revDaily = (clone self::liveOrders())->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, SUM(total_amount) as t')->groupBy('d')->pluck('t', 'd');
        foreach ($revDaily as $d => $t) {
            if (array_key_exists($d, $daily)) {
                $daily[$d] = (float) $t;
            }
        }

        $pct = fn ($cur, $prev) => $prev > 0 ? round(($cur - $prev) / $prev * 100, 1) : ($cur > 0 ? 100 : 0);

        return [
            'rev30' => $rev30,
            'cnt30' => $cnt30,
            'profit30' => $profit30,
            'margin30' => $margin30,
            'retCount' => $retCount,
            'retRate' => $retRate,
            'pctRev' => $pct($rev30, (float) $revPrev),
            'pctCnt' => $pct($cnt30, $cntPrev),
            'sparkLabels' => array_keys($daily),
            'sparkData' => array_values($daily),
        ];
    }

    public static function salesReports(Request $r): array
    {
        $start = Carbon::today()->subDays(29)->startOfDay();

        $dailyRev = self::last30Days();
        $dailyCnt = self::last30Days();
        $rows = (clone self::liveOrders())->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c, SUM(total_amount) as t')
            ->groupBy('d')->get()->keyBy('d');
        foreach (array_keys($dailyRev) as $d) {
            $dailyRev[$d] = (float) ($rows[$d]->t ?? 0);
            $dailyCnt[$d] = (int) ($rows[$d]->c ?? 0);
        }

        $aovDaily = [];
        foreach (array_keys($dailyRev) as $d) {
            $aovDaily[] = $dailyCnt[$d] > 0 ? round($dailyRev[$d] / $dailyCnt[$d], 2) : 0;
        }

        $rev = array_sum($dailyRev);
        $cnt = array_sum($dailyCnt);
        $aov = $cnt > 0 ? round($rev / $cnt, 2) : 0;

        $topProducts = OrderItem::whereHas('order', fn ($q) => $q->where('order_status', '!=', 'cancelled')->where('created_at', '>=', $start))
            ->with(['product.category'])
            ->selectRaw('product_id, SUM(quantity) as qty, SUM(subtotal) as revenue')
            ->groupBy('product_id')->orderByDesc('revenue')->limit(10)->get();

        $table = [];
        foreach (array_keys($dailyRev) as $d) {
            $table[] = [
                'date' => $d,
                'orders' => $dailyCnt[$d],
                'revenue' => $dailyRev[$d],
                'aov' => $dailyCnt[$d] > 0 ? round($dailyRev[$d] / $dailyCnt[$d], 2) : 0,
            ];
        }
        $table = array_reverse($table);

        return [
            'rev' => $rev,
            'cnt' => $cnt,
            'aov' => $aov,
            'labels' => array_keys($dailyRev),
            'revDaily' => array_values($dailyRev),
            'cntDaily' => array_values($dailyCnt),
            'aovDaily' => $aovDaily,
            'topProducts' => $topProducts,
            'table' => $table,
        ];
    }

    public static function profitMargins(Request $r): array
    {
        $start = Carbon::today()->subDays(29)->startOfDay();

        $perProduct = OrderItem::whereHas('order', fn ($q) => $q->where('order_status', '!=', 'cancelled')->where('created_at', '>=', $start))
            ->with(['product.category'])
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as qty, SUM(order_items.subtotal) as revenue, SUM(order_items.quantity * products.cost_price) as cogs')
            ->groupBy('order_items.product_id')
            ->get()
            ->map(function ($row) {
                $row->revenue = (float) $row->revenue;
                $row->cogs = (float) $row->cogs;
                $row->profit = $row->revenue - $row->cogs;
                $row->margin = $row->revenue > 0 ? round($row->profit / $row->revenue * 100, 1) : 0;

                return $row;
            });

        $rev = (float) $perProduct->sum('revenue');
        $cogs = (float) $perProduct->sum('cogs');
        $profit = $rev - $cogs;

        $perCategory = $perProduct->groupBy(fn ($row) => $row->product->category->name ?? 'Tanpa kategori')
            ->map(function ($rows, $name) {
                $revenue = (float) $rows->sum('revenue');
                $cogs = (float) $rows->sum('cogs');
                $p = $revenue - $cogs;

                return [
                    'name' => $name,
                    'revenue' => $revenue,
                    'profit' => $p,
                    'margin' => $revenue > 0 ? round($p / $revenue * 100, 1) : 0,
                ];
            })->sortByDesc('profit')->values();

        return [
            'rev' => $rev,
            'cogs' => $cogs,
            'profit' => $profit,
            'margin' => $rev > 0 ? round($profit / $rev * 100, 1) : 0,
            'topProducts' => $perProduct->sortByDesc('profit')->take(20)->values(),
            'lowProducts' => $perProduct->filter(fn ($x) => $x->revenue > 0)->sortBy('margin')->take(10)->values(),
            'catNames' => $perCategory->pluck('name')->all(),
            'catMargins' => $perCategory->pluck('margin')->all(),
            'catProfits' => $perCategory->pluck('profit')->all(),
        ];
    }

    public static function profile(Request $r): array
    {
        $user = $r->user();
        $user->loadMissing(['outlets']);

        $shifts = Shift::where('user_id', $user->id)->orderByDesc('started_at')->limit(5)->get();

        $monthStart = Carbon::now()->startOfMonth();
        $mtd = (clone self::liveOrders())
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $monthStart);
        $mtdCount = (clone $mtd)->count();
        $mtdSales = (float) (clone $mtd)->sum('total_amount');

        return [
            'user' => $user,
            'outletNames' => $user->outlets->pluck('name')->all(),
            'roleLabel' => ucfirst((string) ($user->role ?? '-')),
            'shifts' => $shifts,
            'mtdCount' => $mtdCount,
            'mtdSales' => $mtdSales,
        ];
    }

    public static function settingsGeneral(Request $r): array
    {
        return [
            'appName' => SystemSetting::getAppName(),
            'orderTypes' => SystemSetting::getOrderTypes(),
            'serialDefault' => SystemSetting::getSerialTrackingDefault(),
            'logoUrl' => SystemSetting::getLogoUrl(),
        ];
    }

    public static function settingsAppearance(Request $r): array
    {
        return [];
    }
}

