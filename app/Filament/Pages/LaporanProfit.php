<?php

namespace App\Filament\Pages;

use App\Models\Outlet;
use App\Services\AdvancedReportService;
use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class LaporanProfit extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-trending-up';

    protected static ?string $title = 'Laporan Profit & Stok Mati';

    protected string $view = 'filament.pages.laporan-profit';

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

    public function getProfitRowsProperty(): array
    {
        return AdvancedReportService::profitRows($this->startDate, $this->endDate, $this->outletId);
    }

    public function getSlowRowsProperty(): array
    {
        return AdvancedReportService::slowRows($this->outletId);
    }

    public function getTotalOmzetProperty(): float
    {
        return collect($this->profitRows)->sum(fn ($r) => (float) $r[4]);
    }

    public function getTotalLabaProperty(): float
    {
        return collect($this->profitRows)->sum(fn ($r) => (float) $r[6]);
    }
}
