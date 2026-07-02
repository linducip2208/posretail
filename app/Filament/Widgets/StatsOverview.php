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

    protected ?string $pollingInterval = '30s';

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

        $totalProducts = Product::where('active', true)->count();

        $totalCustomers = Customer::where('active', true)->count();

        $lowStock = Product::where('active', true)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->count();

        return [
            Stat::make('Pendapatan Hari Ini', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description($todayOrders . ' transaksi')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Produk', $totalProducts)
                ->description($lowStock . ' stok rendah')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStock > 0 ? 'warning' : 'success'),

            Stat::make('Total Pelanggan', $totalCustomers)
                ->description('Pelanggan aktif')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Stok Rendah', $lowStock)
                ->description('Butuh restock')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color($lowStock > 0 ? 'danger' : 'success'),
        ];
    }
}
