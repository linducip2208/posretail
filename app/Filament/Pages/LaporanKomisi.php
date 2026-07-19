<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

class LaporanKomisi extends Page
{
    protected static string|UnitEnum|null $navigationGroup = '📈 Laporan';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $title = 'Laporan Komisi';

    protected string $view = 'filament.pages.laporan-komisi';

    public string $startDate;

    public string $endDate;

    public ?int $outletId = null;

    public ?int $userId = null;

    public function mount(): void
    {
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function getOutletsProperty()
    {
        return auth()->user()?->accessibleOutlets()?->get() ?? Outlet::where('active', true)->orderBy('name')->get();
    }

    public function getUsersProperty()
    {
        return User::whereNotNull('role')->orderBy('name')->get();
    }

    public function getTotalKomisiProperty()
    {
        return (float) $this->commissionQuery()->sum('commission_amount');
    }

    public function getTotalTransaksiProperty()
    {
        return $this->commissionQuery()->count();
    }

    public function getRataKomisiProperty()
    {
        $count = $this->commissionQuery()->count();

        return $count > 0 ? $this->totalKomisi / $count : 0;
    }

    public function getKomisiPerUserProperty()
    {
        return $this->commissionQuery()
            ->selectRaw('user_id, users.name as user_name, users.commission_percent, COUNT(*) as total_orders, SUM(total_amount) as total_sales, SUM(commission_amount) as total_commission')
            ->groupBy('user_id', 'users.name', 'users.commission_percent')
            ->orderByDesc('total_commission')
            ->get();
    }

    public function getKomisiDetailProperty()
    {
        return $this->commissionQuery()
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->leftJoin('outlets', 'orders.outlet_id', '=', 'outlets.id')
            ->select([
                'orders.id',
                'orders.order_number',
                'orders.total_amount',
                'orders.commission_amount',
                'orders.created_at',
                'users.name as user_name',
                'users.commission_percent',
                'customers.name as customer_name',
                'outlets.name as outlet_name',
            ])
            ->orderByDesc('orders.created_at')
            ->get();
    }

    public function getChartLabelsProperty(): array
    {
        return $this->commissionQuery()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as period")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('period')
            ->toArray();
    }

    public function getChartDataProperty(): array
    {
        return $this->commissionQuery()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as period, SUM(commission_amount) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    protected function commissionQuery()
    {
        return Order::where('order_status', 'completed')
            ->where('commission_amount', '>', 0)
            ->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59'])
            ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId))
            ->when($this->userId, fn ($q) => $q->where('user_id', $this->userId));
    }
}
