<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\SupplierPayable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class KpiDashboardWidget extends BaseWidget
{
    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '300s';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'admin']);
    }

    protected function getStats(): array
    {
        // GMROI = Gross Profit / Average Inventory Cost
        $periodStart = now()->subMonths(3);
        $grossProfit = (float) DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'completed')
            ->where('orders.created_at', '>=', $periodStart)
            ->selectRaw('SUM(order_items.subtotal - (order_items.quantity * products.cost_price)) as profit')
            ->value('profit');

        $avgInventory = (float) Product::where('active', true)
            ->selectRaw('AVG(current_stock * cost_price) as avg_val')->value('avg_val');

        $gmroi = $avgInventory > 0 ? round($grossProfit / $avgInventory * 100, 1) : 0;

        // Stock Turnover = COGS / Average Inventory
        $cogs = (float) DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'completed')
            ->where('orders.created_at', '>=', $periodStart)
            ->selectRaw('SUM(order_items.quantity * products.cost_price) as cogs')
            ->value('cogs');

        $turnover = $avgInventory > 0 ? round($cogs / $avgInventory, 2) : 0;

        // Sell-through rate = Units sold / (Units sold + Current stock) * 100
        $sold = (int) DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'completed')
            ->where('orders.created_at', '>=', $periodStart)
            ->sum('order_items.quantity');

        $current = (int) Product::where('active', true)->sum('current_stock');
        $sellThrough = ($sold + $current) > 0 ? round($sold / ($sold + $current) * 100, 1) : 0;

        // Average Basket Size
        $avgBasket = (float) Order::where('order_status', 'completed')
            ->where('created_at', '>=', $periodStart)
            ->avg('total_amount');
        $avgBasket = round($avgBasket ?? 0, 0);

        // AR Aging — total overdue
        $overdueAR = (float) SupplierPayable::where('status', 'pending')
            ->where('due_date', '<', now())
            ->sum('amount');

        return [
            Stat::make('GMROI', $gmroi . '%')
                ->description('Gross Margin Return on Inventory')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($gmroi >= 200 ? 'success' : ($gmroi >= 100 ? 'warning' : 'danger')),

            Stat::make('Stock Turnover', $turnover . 'x')
                ->description('Perputaran stok 3 bulan')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color($turnover >= 4 ? 'success' : ($turnover >= 2 ? 'warning' : 'danger')),

            Stat::make('Sell-Through', $sellThrough . '%')
                ->description('Dari total stok tersedia')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($sellThrough >= 60 ? 'success' : ($sellThrough >= 30 ? 'warning' : 'danger')),

            Stat::make('Avg Basket', 'Rp ' . number_format($avgBasket, 0, ',', '.'))
                ->description('Rata-rata per transaksi')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Overdue AR', 'Rp ' . number_format($overdueAR, 0, ',', '.'))
                ->description('Hutang supplier jatuh tempo')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($overdueAR > 0 ? 'danger' : 'success'),
        ];
    }
}
