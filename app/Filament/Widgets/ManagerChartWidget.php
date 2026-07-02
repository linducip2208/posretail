<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class ManagerChartWidget extends ChartWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    protected ?string $heading = 'Pendapatan 30 Hari Terakhir';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'admin']);
    }

    protected function getData(): array
    {
        $revenue = Order::where('order_status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => $revenue->pluck('total')->toArray(),
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    'borderColor' => '#4f46e5',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $revenue->pluck('date')->map(fn ($d) => date('d M', strtotime($d)))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => '(value) => "Rp " + new Intl.NumberFormat("id-ID").format(value)',
                    ],
                ],
            ],
        ];
    }
}
