<?php

namespace App\Filament\Pages;

use App\Models\Outlet;
use App\Services\AdvancedReportService;
use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class LaporanPelangganPromo extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 12;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-users';

    protected static ?string $title = 'Pelanggan & Promo';

    protected string $view = 'filament.pages.laporan-pelanggan-promo';

    public string $startDate;

    public string $endDate;

    public ?int $outletId = null;

    public function mount(): void
    {
        $this->startDate = now()->subDays(90)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function getOutletsProperty()
    {
        return auth()->user()?->accessibleOutlets()?->get() ?? Outlet::where('active', true)->orderBy('name')->get();
    }

    public function getRfmRowsProperty(): array
    {
        return AdvancedReportService::rfmRows($this->startDate, $this->endDate, $this->outletId);
    }

    public function getPromoRowsProperty(): array
    {
        return AdvancedReportService::promoRows($this->startDate, $this->endDate, $this->outletId);
    }
}
