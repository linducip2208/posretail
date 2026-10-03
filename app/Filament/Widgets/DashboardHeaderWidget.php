<?php

namespace App\Filament\Widgets;

use App\Models\Outlet;
use Filament\Widgets\Widget;

class DashboardHeaderWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-header';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        return (bool) auth()->check();
    }

    public function getViewData(): array
    {
        $today = now()->format('Y-m-d');

        return [
            'dateLabel' => now()->translatedFormat('l, d F Y'),
            'outletCount' => Outlet::where('active', true)->count(),
            'exportSalesUrl' => route('export.sales', ['start_date' => $today, 'end_date' => $today, 'format' => 'csv']),
            'exportItemsUrl' => route('export.sales.items', ['start_date' => $today, 'end_date' => $today, 'format' => 'csv']),
        ];
    }
}
