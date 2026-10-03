<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        $role = auth()->user()?->role;
        return in_array($role, ['owner', 'manager', 'admin', 'gudang']);
    }

    protected function getStats(): array
    {
        $todayRevenue = Order::whereDate('created_at', today())
            ->where('order_status', 'completed')
            ->sum('total_amount');

        $todayOrders = Order::whereDate('created_at', today())
            ->where('order_status', 'completed')
            ->count();

        $yesterdayRevenue = Order::whereDate('created_at', today()->subDay())
            ->where('order_status', 'completed')
            ->sum('total_amount');

        $yesterdayOrders = Order::whereDate('created_at', today()->subDay())
            ->where('order_status', 'completed')
            ->count();

        $totalProducts = Product::where('active', true)->count();

        $totalCustomers = Customer::where('active', true)->count();

        $lowStock = Product::where('active', true)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->count();

        $outOfStock = Product::where('active', true)
            ->where('current_stock', '<=', 0)
            ->count();

        $avgOrder = $todayOrders > 0 ? $todayRevenue / $todayOrders : 0;

        $pendingPayments = Order::where('order_status', '!=', 'cancelled')
            ->whereIn('payment_status', ['pending', 'partial'])
            ->count();

        return [
            Stat::make('Pendapatan Hari Ini', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description(self::trendDesc($todayRevenue, $yesterdayRevenue, 'dari kemarin'))
                ->descriptionIcon('tabler-trending-up')
                ->color('success'),

            Stat::make('Transaksi Hari Ini', $todayOrders . ' transaksi')
                ->description(self::trendDesc($todayOrders, $yesterdayOrders, 'dari kemarin'))
                ->descriptionIcon('tabler-shopping-cart')
                ->color('primary'),

            Stat::make('Rata-rata Transaksi', 'Rp ' . number_format($avgOrder, 0, ',', '.'))
                ->description('Hari ini')
                ->descriptionIcon('tabler-calculator')
                ->color('primary'),

            Stat::make('Pembayaran Pending', $pendingPayments)
                ->description('Butuh tindak lanjut')
                ->descriptionIcon('tabler-clock')
                ->color($pendingPayments > 0 ? 'warning' : 'success'),

            Stat::make('Total Produk', $totalProducts)
                ->description($lowStock . ' stok rendah')
                ->descriptionIcon('tabler-alert-triangle')
                ->color($lowStock > 0 ? 'warning' : 'success'),

            Stat::make('Total Pelanggan', $totalCustomers)
                ->description('Pelanggan aktif')
                ->descriptionIcon('tabler-users')
                ->color('primary'),

            Stat::make('Stok Habis', $outOfStock)
                ->description('Butuh restock segera')
                ->descriptionIcon('tabler-circle-x')
                ->color($outOfStock > 0 ? 'danger' : 'success'),
        ];
    }

    protected static function trendDesc(float $today, float $yesterday, string $suffix): string
    {
        if ($yesterday <= 0) {
            return $today > 0 ? 'Hari pertama bertransaksi' : 'Belum ada data ' . $suffix;
        }

        $pct = (($today - $yesterday) / $yesterday) * 100;
        $arrow = $pct >= 0 ? '↑' : '↓';

        return sprintf('%s %.1f%% %s', $arrow, abs($pct), $suffix);
    }
}
