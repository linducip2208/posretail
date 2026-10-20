<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use ShopApi;

    private function period(Request $request): ?JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'outlet_id' => 'nullable|integer|exists:outlets,id',
        ]);

        $start = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $end = $request->input('end_date', now()->format('Y-m-d'));

        if (\Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) > 93) {
            return response()->json(['message' => 'Rentang tanggal maksimal 93 hari.'], 422);
        }

        return null;
    }

    private function scopedOrders(Request $request, string $start, string $end)
    {
        $q = Order::where('order_status', 'completed')
            ->whereBetween('created_at', [$start, $end . ' 23:59:59'])
            ->whereIn('outlet_id', $this->outletIds($request));
        if ($request->filled('outlet_id')) {
            $oid = (int) $request->outlet_id;
            if (! in_array($oid, $this->outletIds($request), true)) {
                return null;
            }
            $q->where('outlet_id', $oid);
        }

        return $q;
    }

    public function sales(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'laporan')) {
            return $deny;
        }
        if ($err = $this->period($request)) {
            return $err;
        }
        $start = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $end = $request->input('end_date', now()->format('Y-m-d'));
        $orders = $this->scopedOrders($request, $start, $end);
        if ($orders === null) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }

        $revenue = (float) (clone $orders)->sum('total_amount');
        $count = (int) (clone $orders)->count();
        $top = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.order_status', 'completed')
            ->whereBetween('orders.created_at', [$start, $end . ' 23:59:59'])
            ->whereIn('orders.outlet_id', $this->outletIds($request))
            ->selectRaw('products.name, SUM(order_items.quantity) as qty, SUM(order_items.subtotal) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')->limit(10)->get();

        return response()->json(['data' => [
            'start_date' => $start, 'end_date' => $end,
            'revenue' => $revenue, 'transactions' => $count,
            'average' => $count > 0 ? round($revenue / $count, 2) : 0,
            'top_products' => $top,
        ]]);
    }

    public function stock(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'laporan')) {
            return $deny;
        }
        $ids = $this->outletIds($request);

        $value = (float) Product::where('active', true)->sum(DB::raw('current_stock * cost_price'));
        $low = Product::where('active', true)->whereColumn('current_stock', '<=', 'min_stock')->count();
        $out = Product::where('active', true)->where('current_stock', '<=', 0)->count();

        return response()->json(['data' => [
            'inventory_value' => $value,
            'low_stock' => $low,
            'out_of_stock' => $out,
            'outlet_scope' => $ids,
        ]]);
    }

    public function finance(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'laporan')) {
            return $deny;
        }
        if ($err = $this->period($request)) {
            return $err;
        }
        $start = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $end = $request->input('end_date', now()->format('Y-m-d'));
        $orders = $this->scopedOrders($request, $start, $end);
        if ($orders === null) {
            return response()->json(['message' => 'Akses ditolak untuk outlet ini.'], 403);
        }

        $income = (float) (clone $orders)->sum('total_amount');
        $expense = (float) \App\Models\Expense::whereBetween('expense_date', [$start, $end])
            ->whereIn('outlet_id', $this->outletIds($request))->sum('amount');

        return response()->json(['data' => [
            'start_date' => $start, 'end_date' => $end,
            'income' => $income, 'expense' => $expense,
            'net' => $income - $expense,
        ]]);
    }
}
