<?php

namespace App\Filament\Pages;

use App\Models\Outlet;
use App\Services\AdvancedReportService;
use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class LaporanKas extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 11;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-wallet';

    protected static ?string $title = 'Arus Kas, Hutang & Pajak';

    protected string $view = 'filament.pages.laporan-kas';

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

    public function getCashflowRowsProperty(): array
    {
        return AdvancedReportService::cashflowRows($this->startDate, $this->endDate, $this->outletId);
    }

    public function getPayableRowsProperty(): array
    {
        return AdvancedReportService::payableRows($this->outletId);
    }

    public function getTaxRowsProperty(): array
    {
        return AdvancedReportService::taxRows($this->startDate, $this->endDate, $this->outletId);
    }

    public function getTotalMasukProperty(): float
    {
        return collect($this->cashflowRows)->sum(fn ($r) => (float) $r[1]);
    }

    public function getTotalKeluarProperty(): float
    {
        return collect($this->cashflowRows)->sum(fn ($r) => (float) $r[2]);
    }

    public function getTotalHutangProperty(): float
    {
        return collect($this->payableRows)->sum(fn ($r) => (float) $r[4]);
    }
}
