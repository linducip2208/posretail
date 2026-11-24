<?php

namespace App\Services\Retail;

use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\PriceChange;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Models\Unit;
use App\Models\WriteOff;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/** DATA ASLI halaman Catalog + Inventory. */
class CatalogData
{
    public static function productsList(Request $r): array
    {
        $q = (string) $r->input('q', '');
        $cat = (string) $r->input('category', '');

        $query = Product::with(['category', 'brand', 'unit'])
            ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('sku', 'like', "%{$q}%")
                ->orWhere('barcode', 'like', "%{$q}%")))
            ->when($cat !== '', fn ($x) => $x->where('category_id', $cat))
            ->orderBy('name');

        $per = 48;
        $page = max(1, (int) $r->input('page', 1));
        $total = (clone $query)->count();
        $items = $query->skip(($page - 1) * $per)->take($per)->get();
        $products = new LengthAwarePaginator($items, $total, $per, $page, [
            'path' => $r->url(), 'query' => $r->query(),
        ]);

        $invValue = (float) Product::selectRaw('COALESCE(SUM(current_stock * cost_price),0) as v')->value('v');
        $low = Product::whereColumn('current_stock', '<=', 'min_stock')->where('current_stock', '>', 0)->count();
        $out = Product::where('current_stock', '<=', 0)->count();

        return [
            'products' => $products,
            'q' => $q,
            'category' => $cat,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'totalActive' => Product::count(),
            'invValue' => $invValue,
            'lowCount' => $low,
            'outCount' => $out,
        ];
    }

    public static function productDetails(Request $r): array
    {
        $id = $r->input('id');
        $product = Product::with(['category', 'brand', 'unit', 'outlet', 'variants'])->find($id);
        if (! $product) {
            abort(404);
        }
        $movements = StockMovement::with(['outlet', 'productVariant'])
            ->where('product_id', $product->id)->latest()->take(20)->get();
        $sales = OrderItem::with(['order' => fn ($o) => $o->select('id', 'order_number', 'created_at')])
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($o) => $o->where('order_status', 'completed'))
            ->latest()->take(5)->get();
        $soldQty = (int) OrderItem::where('product_id', $product->id)
            ->whereHas('order', fn ($o) => $o->where('order_status', 'completed'))->sum('quantity');
        $soldRev = (float) OrderItem::where('product_id', $product->id)
            ->whereHas('order', fn ($o) => $o->where('order_status', 'completed'))->sum('subtotal');
        $cost = (float) $product->cost_price;
        $price = (float) $product->selling_price;
        $margin = $price > 0 ? ($price - $cost) / $price * 100 : 0;

        return compact('product', 'movements', 'sales', 'soldQty', 'soldRev', 'margin');
    }

    public static function productCategories(Request $r): array
    {
        $categories = Category::with(['products' => fn ($x) => $x->select(
            'id', 'category_id', 'name', 'current_stock', 'cost_price', 'selling_price'
        )])->orderBy('name')->get()->map(function ($c) {
            $c->product_count = $c->products->count();
            $c->stock_value = $c->products->sum(fn ($p) => (float) $p->current_stock * (float) $p->cost_price);
            $c->retail_value = $c->products->sum(fn ($p) => (float) $p->current_stock * (float) $p->selling_price);

            return $c;
        });

        return [
            'categories' => $categories,
            'totalProducts' => Product::count(),
            'totalValue' => $categories->sum('stock_value'),
        ];
    }

    public static function productVariants(Request $r): array
    {
        $variants = ProductVariant::with('product')->latest()->take(60)->get();

        return [
            'variants' => $variants,
            'totalVariants' => ProductVariant::count(),
            'totalStock' => (int) ProductVariant::sum('current_stock'),
            'productsWithVariants' => Product::where('has_variants', true)->count(),
        ];
    }

    public static function productPricing(Request $r): array
    {
        $products = Product::with('category')->orderBy('name')->take(60)->get();
        $changes = PriceChange::with(['product', 'user'])->latest()->take(20)->get();
        $avgMargin = $products->filter(fn ($p) => (float) $p->selling_price > 0)
            ->avg(fn ($p) => ((float) $p->selling_price - (float) $p->cost_price) / (float) $p->selling_price * 100);

        return [
            'products' => $products,
            'changes' => $changes,
            'avgMargin' => (float) ($avgMargin ?? 0),
        ];
    }

    public static function inventoryOverview(Request $r): array
    {
        $totalSku = Product::count();
        $costValue = (float) Product::selectRaw('COALESCE(SUM(current_stock * cost_price),0) as v')->value('v');
        $retailValue = (float) Product::selectRaw('COALESCE(SUM(current_stock * selling_price),0) as v')->value('v');
        $lowCount = Product::whereColumn('current_stock', '<=', 'min_stock')->where('current_stock', '>', 0)->count();
        $outCount = Product::where('current_stock', '<=', 0)->count();

        $byCategory = Category::with(['products' => fn ($x) => $x->select('id', 'category_id', 'current_stock', 'cost_price')])
            ->get()->map(fn ($c) => [
                'name' => $c->name,
                'value' => $c->products->sum(fn ($p) => (float) $p->current_stock * (float) $p->cost_price),
                'count' => $c->products->count(),
            ])->sortByDesc('value')->values();

        $topLow = Product::with('category')->where('current_stock', '>', 0)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->orderBy('current_stock')->take(5)->get();

        return compact('totalSku', 'costValue', 'retailValue', 'lowCount', 'outCount', 'byCategory', 'topLow');
    }

    public static function stockLevels(Request $r): array
    {
        $q = (string) $r->input('q', '');
        $outlet = (string) $r->input('outlet', '');

        $products = Product::with(['category', 'unit'])
            ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")))
            ->when($outlet !== '', fn ($x) => $x->where('outlet_id', $outlet))
            ->orderBy('current_stock')->take(60)->get();

        return [
            'products' => $products,
            'q' => $q,
            'outlet' => $outlet,
            'outlets' => Outlet::orderBy('name')->get(['id', 'name']),
            'totalUnits' => (int) Product::sum('current_stock'),
        ];
    }

    public static function stockAdjustments(Request $r): array
    {
        $opnames = StockOpname::with(['outlet', 'user'])->latest()->take(30)->get();
        $writeoffs = WriteOff::with(['product', 'outlet', 'user'])->latest()->take(20)->get();

        $activity = $opnames->map(fn ($o) => [
            'date' => $o->created_at, 'type' => 'Stock Opname',
            'ref' => $o->opname_number, 'outlet' => $o->outlet->name ?? '-',
            'user' => $o->user->name ?? '-', 'status' => $o->status,
            'detail' => $o->notes,
        ])->concat($writeoffs->map(fn ($w) => [
            'date' => $w->created_at, 'type' => 'Write-Off',
            'ref' => $w->writeoff_number, 'outlet' => $w->outlet->name ?? '-',
            'user' => $w->user->name ?? '-', 'status' => 'completed',
            'detail' => ($w->product->name ?? '-') . ' × ' . $w->quantity . ' (' . ($w->reason ?? '-') . ')',
        ]))->sortByDesc('date')->values();

        return [
            'opnames' => $opnames,
            'writeoffs' => $writeoffs,
            'activity' => $activity,
            'totalOpname' => StockOpname::count(),
            'totalLoss' => (float) WriteOff::sum('total_loss'),
        ];
    }

    public static function stockTransfers(Request $r): array
    {
        $transfers = StockTransfer::with(['fromOutlet', 'toOutlet', 'user', 'stockTransferItems'])
            ->latest()->take(50)->get();

        return [
            'transfers' => $transfers,
            'pending' => StockTransfer::where('status', 'pending')->count(),
            'inTransit' => StockTransfer::whereIn('status', ['approved', 'in_transit', 'sent'])->count(),
            'completed' => StockTransfer::where('status', 'completed')->count(),
        ];
    }

    public static function lowStockAlerts(Request $r): array
    {
        $items = Product::with(['category', 'unit'])
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->orderBy('current_stock')->get();

        return [
            'items' => $items,
            'outOfStock' => $items->where('current_stock', '<=', 0)->values(),
            'critical' => $items->filter(fn ($p) => $p->current_stock > 0 && $p->current_stock <= max(1, (int) floor((float) $p->min_stock / 2)))->values(),
            'low' => $items->filter(fn ($p) => $p->current_stock > max(1, (int) floor((float) $p->min_stock / 2)))->values(),
        ];
    }

    public static function inventoryHistory(Request $r): array
    {
        $movements = StockMovement::with(['product', 'productVariant', 'outlet'])
            ->latest()->take(100)->get();

        return [
            'movements' => $movements,
            'inQty' => (int) StockMovement::where('type', 'in')->sum('quantity'),
            'outQty' => (int) StockMovement::where('type', 'out')->sum('quantity'),
        ];
    }

    public static function productPerformance(Request $r): array
    {
        $from = now()->subDays(30);
        $rows = OrderItem::select('product_id',
                DB::raw('SUM(quantity) as qty'), DB::raw('SUM(subtotal) as revenue'))
            ->whereHas('order', fn ($o) => $o->where('order_status', 'completed')->where('created_at', '>=', $from))
            ->groupBy('product_id')->get()->keyBy('product_id');

        $products = Product::with('category')->whereIn('id', $rows->keys()->all())->get()
            ->map(fn ($p) => tap($p, fn () => $p->setAttribute('perf_qty', (int) $rows[$p->id]->qty)
                ->setAttribute('perf_revenue', (float) $rows[$p->id]->revenue)))
            ->values();

        return [
            'topQty' => $products->sortByDesc('perf_qty')->take(10)->values(),
            'bottomQty' => $products->sortBy('perf_qty')->take(10)->values(),
            'topRevenue' => $products->sortByDesc('perf_revenue')->take(10)->values(),
            'bottomRevenue' => $products->sortBy('perf_revenue')->take(10)->values(),
            'days' => 30,
        ];
    }

    public static function inventoryReports(Request $r): array
    {
        $valuation = Category::with(['products' => fn ($x) => $x->select(
            'id', 'category_id', 'current_stock', 'cost_price', 'selling_price'
        )])->orderBy('name')->get()->map(function ($c) {
            $cost = $c->products->sum(fn ($p) => (float) $p->current_stock * (float) $p->cost_price);
            $retail = $c->products->sum(fn ($p) => (float) $p->current_stock * (float) $p->selling_price);

            return [
                'name' => $c->name,
                'sku' => $c->products->count(),
                'units' => (int) $c->products->sum('current_stock'),
                'cost' => $cost,
                'retail' => $retail,
                'profit' => $retail - $cost,
            ];
        })->values();

        $soldIds = OrderItem::whereHas('order', fn ($o) => $o
            ->where('order_status', 'completed')->where('created_at', '>=', now()->subDays(60)))
            ->distinct()->pluck('product_id')->all();
        $slow = Product::with('category')->whereNotIn('id', $soldIds ?: [0])
            ->get()->sortByDesc(fn ($p) => (float) $p->current_stock * (float) $p->cost_price)
            ->take(20)->values();

        return [
            'valuation' => $valuation,
            'slow' => $slow,
            'totalCost' => collect($valuation)->sum('cost'),
            'totalRetail' => collect($valuation)->sum('retail'),
        ];
    }

    public static function productCreateForm(Request $r): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'units' => Unit::orderBy('name')->get(['id', 'name']),
        ];
    }
}

