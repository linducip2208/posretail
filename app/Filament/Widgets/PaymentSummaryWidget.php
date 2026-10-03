<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class PaymentSummaryWidget extends Widget
{
    protected string $view = 'filament.widgets.payment-summary';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 4;

    protected ?string $pollingInterval = '300s';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'admin', 'kasir']);
    }

    public function getViewData(): array
    {
        $rows = DB::table('payments')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->whereDate('orders.created_at', today())
            ->where('orders.order_status', '!=', 'cancelled')
            ->whereIn('payments.status', ['success', 'confirmed', 'completed'])
            ->selectRaw('payment_methods.name as method, COUNT(*) as transactions, SUM(payments.amount) as total')
            ->groupBy('payment_methods.id', 'payment_methods.name')
            ->orderByDesc('total')
            ->get();

        return ['rows' => $rows, 'grandTotal' => (float) $rows->sum('total')];
    }
}
