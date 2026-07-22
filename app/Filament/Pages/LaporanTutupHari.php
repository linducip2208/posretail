<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Expense;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Shift;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use BackedEnum;
use UnitEnum;

class LaporanTutupHari extends Page
{
    protected static string|UnitEnum|null $navigationGroup = '📈 Laporan';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $title = 'Tutup Hari';

    protected string $view = 'filament.pages.laporan-tutup-hari';

    public string $date;

    public ?int $outletId = null;

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function getOutletsProperty()
    {
        return auth()->user()?->accessibleOutlets()?->get() ?? Outlet::where('active', true)->orderBy('name')->get();
    }

    public function getSummaryProperty(): array
    {
        $totalSales = (float) Order::whereDate('created_at', $this->date)
            ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId))
            ->where('order_status', 'completed')
            ->sum('total_amount');

        $totalOrders = Order::whereDate('created_at', $this->date)
            ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId))
            ->where('order_status', 'completed')
            ->count();

        $cashPayments = (float) DB::table('payments')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->whereDate('orders.created_at', $this->date)
            ->when($this->outletId, fn ($q) => $q->where('orders.outlet_id', $this->outletId))
            ->whereIn('payments.status', ['success', 'confirmed', 'completed'])
            ->where(fn ($q) => $q->where('payment_methods.name', 'like', '%cash%')
                ->orWhere('payment_methods.name', 'like', '%tunai%'))
            ->sum('payments.amount');

        $nonCashPayments = (float) DB::table('payments')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->whereDate('orders.created_at', $this->date)
            ->when($this->outletId, fn ($q) => $q->where('orders.outlet_id', $this->outletId))
            ->whereIn('payments.status', ['success', 'confirmed', 'completed'])
            ->where(fn ($q) => $q->where('payment_methods.name', 'not like', '%cash%')
                ->where('payment_methods.name', 'not like', '%tunai%'))
            ->sum('payments.amount');

        $totalExpenses = 0;
        if (Schema::hasTable('expenses')) {
            $totalExpenses = (float) Expense::whereDate('expense_date', $this->date)
                ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId))
                ->sum('amount');
        }

        $activeShifts = Shift::with('user')
            ->whereDate('started_at', $this->date)
            ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId))
            ->get();

        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'cash' => $cashPayments,
            'non_cash' => $nonCashPayments,
            'expenses' => $totalExpenses,
            'net_cash' => $cashPayments - $totalExpenses,
            'shifts' => $activeShifts,
        ];
    }

    public function getPaymentsByMethodProperty()
    {
        return DB::table('payments')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->whereDate('orders.created_at', $this->date)
            ->when($this->outletId, fn ($q) => $q->where('orders.outlet_id', $this->outletId))
            ->whereIn('payments.status', ['success', 'confirmed', 'completed'])
            ->selectRaw('payment_methods.name as method, SUM(payments.amount) as total, COUNT(*) as count')
            ->groupBy('payment_methods.id', 'payment_methods.name')
            ->orderByDesc('total')
            ->get();
    }
}
