<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Forecast & reorder suggestion (logika sama dengan DemandForecastWidget).
 */
class InventoryController extends Controller
{
    public function forecast(Request $request): JsonResponse
    {
        $weeks = min(24, max(4, (int) $request->integer('weeks', 12)));

        // Ambil agregat mentah (kompatibel MySQL + SQLite), hitung forecast di PHP
        $rows = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.order_status', 'completed')
            ->where('orders.created_at', '>=', now()->subWeeks($weeks))
            ->when($request->outlet_id, fn ($q) => $q->where('orders.outlet_id', $request->outlet_id))
            ->select(
                'products.id',
                'products.name as product_name',
                'products.sku',
                'products.current_stock',
                'products.min_stock',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.current_stock', 'products.min_stock')
            ->orderByDesc('total_sold')
            ->limit(50)
            ->get()
            ->map(function ($r) use ($weeks) {
                $avg = round(((float) $r->total_sold) / $weeks, 1);
                $forecast = (int) ceil($avg * 1.1);
                $r->avg_weekly_sold = $avg;
                $r->forecast_next_week = $forecast;
                $r->suggested_order = max(0, $forecast - (int) $r->current_stock);
                unset($r->total_sold);

                return $r;
            });

        $lowStock = DB::table('products')
            ->where('active', true)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->when($request->outlet_id, fn ($q) => $q->where('outlet_id', $request->outlet_id))
            ->select('id', 'name', 'sku', 'current_stock', 'min_stock')
            ->limit(50)->get();

        return response()->json([
            'weeks' => $weeks,
            'forecast' => $rows,
            'low_stock' => $lowStock,
        ]);
    }
}
