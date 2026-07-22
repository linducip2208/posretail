<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Order;
use App\Models\SupplierPayable;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CashFlowForecastWidget extends ChartWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '600s';

    protected ?string $heading = 'Proyeksi Arus Kas 30 Hari';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager']);
    }

    protected function getData(): array
    {
        $days = 30;
        $labels = [];
        $cashIn = [];
        $cashOut = [];
        $balance = [];
        $running = $this->getCurrentCashBalance();

        for ($i = 0; $i < $days; $i++) {
            $date = now()->addDays($i);
            $labels[] = $date->format('d M');

            $dayIn = $this->getExpectedInflow($date);
            $dayOut = $this->getExpectedOutflow($date);

            $cashIn[] = $dayIn;
            $cashOut[] = $dayOut;

            $running += ($dayIn - $dayOut);
            $balance[] = round($running, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Saldo Proyeksi',
                    'data' => $balance,
                    'type' => 'line',
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.05)',
                    'fill' => true,
                    'tension' => 0.3,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Kas Masuk',
                    'data' => $cashIn,
                    'type' => 'bar',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.6)',
                    'borderRadius' => 4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Kas Keluar',
                    'data' => $cashOut,
                    'type' => 'bar',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.6)',
                    'borderRadius' => 4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'position' => 'left',
                    'title' => ['display' => true, 'text' => 'Saldo (Rp)'],
                    'ticks' => ['callback' => '(v) => "Rp " + new Intl.NumberFormat("id-ID").format(v)'],
                ],
                'y1' => [
                    'type' => 'linear',
                    'position' => 'right',
                    'grid' => ['drawOnChartArea' => false],
                    'title' => ['display' => true, 'text' => 'Arus (Rp)'],
                    'ticks' => ['callback' => '(v) => "Rp " + new Intl.NumberFormat("id-ID").format(v)'],
                ],
            ],
        ];
    }

    private function getCurrentCashBalance(): float
    {
        return (float) DB::table('journal_entry_items')
            ->join('accounts', 'journal_entry_items.account_id', '=', 'accounts.id')
            ->where('accounts.code', '1-1000')
            ->sum(DB::raw('debit - credit'));
    }

    private function getExpectedInflow($date): float
    {
        return (float) Order::where('payment_status', '!=', 'paid')
            ->whereDate('created_at', '>=', $date->copy()->subDays(7))
            ->whereDate('created_at', '<=', $date)
            ->avg('total_amount') ?? 0;
    }

    private function getExpectedOutflow($date): float
    {
        $payables = (float) SupplierPayable::where('status', 'pending')
            ->whereDate('due_date', $date)
            ->sum('amount');

        $avgExpense = 0;
        if (Schema::hasTable('expenses')) {
            $avgExpense = (float) Expense::whereDate('expense_date', '>=', now()->subDays(30))
                ->avg('amount') ?? 0;
        }

        return $payables + ($avgExpense * 0.3);
    }
}
