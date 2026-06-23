<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

class LaporanKeuangan extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $title = 'Laporan Keuangan';

    protected string $view = 'filament.pages.laporan-keuangan';

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
        return Outlet::where('active', true)->orderBy('name')->get();
    }

    public function getTotalRevenueProperty()
    {
        return (float) $this->orderQueryBase()->sum('total_amount');
    }

    public function getTotalExpenseProperty()
    {
        return (float) $this->expenseQueryBase()->sum('total_amount');
    }

    public function getTotalProfitProperty()
    {
        return $this->totalRevenue - $this->totalExpense;
    }

    public function getUnpaidSalesProperty()
    {
        return (float) $this->orderQueryBase()
            ->where('payment_status', '!=', 'paid')
            ->sum('total_amount');
    }

    public function getRevenueByPaymentProperty()
    {
        return DB::table('payments')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate.' 23:59:59'])
            ->when($this->outletId, fn ($q) => $q->where('orders.outlet_id', $this->outletId))
            ->where('payments.status', 'confirmed')
            ->selectRaw('payment_methods.name as method, SUM(payments.amount) as total')
            ->groupBy('payment_methods.id', 'payment_methods.name')
            ->orderByDesc('total')
            ->get();
    }

    public function getChartLabelsProperty(): array
    {
        $format = match ($this->groupBy) {
            'weekly' => '%x-W%v',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $labels = collect();

        $orderLabels = $this->orderQueryBase()
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period")
            ->groupBy('period')
            ->pluck('period');

        $expenseLabels = $this->expenseQueryBase()
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period")
            ->groupBy('period')
            ->pluck('period');

        return $orderLabels->merge($expenseLabels)->unique()->sort()->values()->toArray();
    }

    public function getChartRevenueProperty(): array
    {
        $format = match ($this->groupBy) {
            'weekly' => '%x-W%v',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $revenue = $this->orderQueryBase()
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period, SUM(total_amount) as total")
            ->groupBy('period')
            ->pluck('total', 'period');

        return collect($this->chartLabels)
            ->map(fn ($label) => (float) ($revenue[$label] ?? 0))
            ->toArray();
    }

    public function getChartExpenseProperty(): array
    {
        $format = match ($this->groupBy) {
            'weekly' => '%x-W%v',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $expenses = $this->expenseQueryBase()
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period, SUM(total_amount) as total")
            ->groupBy('period')
            ->pluck('total', 'period');

        return collect($this->chartLabels)
            ->map(fn ($label) => (float) ($expenses[$label] ?? 0))
            ->toArray();
    }

    public function getCashFlowProperty(): array
    {
        $moneyIn = (float) DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->whereBetween('payments.created_at', [$this->startDate, $this->endDate.' 23:59:59'])
            ->when($this->outletId, fn ($q) => $q->where('orders.outlet_id', $this->outletId))
            ->where('payments.status', 'confirmed')
            ->sum('payments.amount');

        $moneyOut = $this->totalExpense;
        $netFlow = $moneyIn - $moneyOut;

        return [
            'money_in' => $moneyIn,
            'money_out' => $moneyOut,
            'net' => $netFlow,
        ];
    }

    protected function orderQueryBase()
    {
        return Order::whereBetween('created_at', [$this->startDate, $this->endDate.' 23:59:59'])
            ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId))
            ->where('order_status', 'completed');
    }

    protected function expenseQueryBase()
    {
        return PurchaseOrder::whereBetween('created_at', [$this->startDate, $this->endDate.' 23:59:59'])
            ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId))
            ->where('status', 'received');
    }
}

