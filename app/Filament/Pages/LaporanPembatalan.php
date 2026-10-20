<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Outlet;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class LaporanPembatalan extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 9;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-circle-x';

    protected static ?string $title = 'Laporan Pembatalan';

    protected string $view = 'filament.pages.laporan-pembatalan';

    public string $startDate;

    public string $endDate;

    public ?int $outletId = null;

    public function mount(): void
    {
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function getOutletsProperty()
    {
        return auth()->user()?->accessibleOutlets()?->get() ?? Outlet::where('active', true)->orderBy('name')->get();
    }

    protected function queryBase()
    {
        return Order::with(['user', 'outlet', 'customer', 'orderItems.product'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate.' 23:59:59'])
            ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId))
            ->where('order_status', 'cancelled');
    }

    public function getCancelledOrdersProperty()
    {
        return $this->queryBase()->latest()->get();
    }

    public function getTotalCancelledProperty()
    {
        return (float) $this->queryBase()->sum('total_amount');
    }

    public function getTotalCountProperty()
    {
        return $this->queryBase()->count();
    }

    public function getTrendLabelsProperty(): array
    {
        return $this->trendBase()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as period")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('period')
            ->toArray();
    }

    public function getTrendDataProperty(): array
    {
        return $this->trendBase()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as period, COUNT(*) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total')
            ->map(fn ($v) => (int) $v)
            ->toArray();
    }

    public function getByOutletProperty()
    {
        return $this->trendBase()
            ->join('outlets', 'orders.outlet_id', '=', 'outlets.id')
            ->selectRaw('outlets.name as outlet, COUNT(*) as total')
            ->groupBy('outlets.id', 'outlets.name')
            ->orderByDesc('total')
            ->get();
    }

    protected function trendBase()
    {
        return \Illuminate\Support\Facades\DB::table('orders')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate.' 23:59:59'])
            ->when($this->outletId, fn ($q) => $q->where('orders.outlet_id', $this->outletId))
            ->where('orders.order_status', 'cancelled');
    }
}
