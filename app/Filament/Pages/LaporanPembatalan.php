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
}
