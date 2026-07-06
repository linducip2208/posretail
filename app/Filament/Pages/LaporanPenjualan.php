<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Outlet;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

class LaporanPenjualan extends Page
{
    protected static string|UnitEnum|null $navigationGroup = '📊 Laporan';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $title = 'Laporan Penjualan';

    protected string $view = 'filament.pages.laporan-penjualan';

    public string $startDate;

    public string $endDate;

    public ?int $outletId = null;

    public string $groupBy = 'daily';

    public function mount(): void
    {
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function getOutletsProperty()
    {
        return auth()->user()?->accessibleOutlets()?->get() ?? Outlet::where('active', true)->orderBy('name')->get();
    }

    public function getTotalSalesProperty()
    {
        return (float) $this->queryBase()->sum('total_amount');
    }

    public function getTotalOrdersProperty()
    {
        return $this->queryBase()->count();
    }

    public function getAvgOrderProperty()
    {
        $count = $this->queryBase()->count();

        return $count > 0 ? $this->totalSales / $count : 0;
    }

    public function getTotalDiscountProperty()
    {
        return (float) $this->queryBase()->sum('discount_amount');
    }

    public function getChartLabelsProperty(): array
    {
        $format = match ($this->groupBy) {
            'weekly' => '%x-W%v',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return $this->queryBase()
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('period')
            ->toArray();
    }

    public function getChartDataProperty(): array
    {
        $format = match ($this->groupBy) {
            'weekly' => '%x-W%v',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return $this->queryBase()
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period, SUM(total_amount) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    public function getTopProductsProperty()
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate.' 23:59:59'])
            ->when($this->outletId, fn ($q) => $q->where('orders.outlet_id', $this->outletId))
            ->where('orders.order_status', 'completed')
            ->selectRaw('products.name, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();
    }

    protected function queryBase()
    {
        return Order::whereBetween('created_at', [$this->startDate, $this->endDate.' 23:59:59'])
            ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId))
            ->where('order_status', 'completed');
    }

    public function getRevenueByPaymentProperty()
    {
        return DB::table('payments')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate.' 23:59:59'])
            ->when($this->outletId, fn ($q) => $q->where('orders.outlet_id', $this->outletId))
            ->whereIn('payments.status', ['success', 'confirmed'])
            ->selectRaw('payment_methods.name as method, SUM(payments.amount) as total')
            ->groupBy('payment_methods.id', 'payment_methods.name')
            ->orderByDesc('total')
            ->get();
    }

    public function getPaymentsDetailProperty()
    {
        return DB::table('payments')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->leftJoin('outlets', 'orders.outlet_id', '=', 'outlets.id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate.' 23:59:59'])
            ->when($this->outletId, fn ($q) => $q->where('orders.outlet_id', $this->outletId))
            ->whereIn('payments.status', ['success', 'confirmed'])
            ->select([
                'orders.order_number',
                'customers.name as customer_name',
                'payment_methods.name as method',
                'payments.amount',
                'payments.created_at',
                'outlets.name as outlet_name',
            ])
            ->orderByDesc('payments.created_at')
            ->get();
    }
}
