<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Outlet;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class LaporanPiutang extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $title = 'Laporan Piutang';

    protected string $view = 'filament.pages.laporan-piutang';

    public string $asOfDate;

    public ?int $outletId = null;

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
    }

    public function getOutletsProperty()
    {
        return auth()->user()?->accessibleOutlets()?->get() ?? Outlet::where('active', true)->orderBy('name')->get();
    }

    protected function queryBase()
    {
        return Order::with(['customer', 'outlet', 'user'])
            ->where('order_status', '!=', 'cancelled')
            ->where('remaining_amount', '>', 0)
            ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId));
    }

    public function getReceivablesProperty()
    {
        return $this->queryBase()->get();
    }

    public function getTotalReceivableProperty()
    {
        return (float) $this->queryBase()->sum('remaining_amount');
    }

    public function getAgingCurrentProperty()
    {
        return (float) (clone $this->queryBase())
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->sum('remaining_amount');
    }

    public function getAging31to60Property()
    {
        return (float) (clone $this->queryBase())
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(31)])
            ->sum('remaining_amount');
    }

    public function getAging61to90Property()
    {
        return (float) (clone $this->queryBase())
            ->whereBetween('created_at', [now()->subDays(90), now()->subDays(61)])
            ->sum('remaining_amount');
    }

    public function getAgingOver90Property()
    {
        return (float) (clone $this->queryBase())
            ->where('created_at', '<', now()->subDays(90))
            ->sum('remaining_amount');
    }

    public function getTotalCountProperty()
    {
        return $this->queryBase()->count();
    }
}
