<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\DashboardWidgetFilter;
use App\Models\Order;
use App\Models\SalesTarget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SalesTargetProgressWidget extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        $role = auth()->user()?->role;
        return in_array($role, ['owner', 'manager', 'admin']);
    }

    public function table(Table $table): Table
    {
        $year = now()->year;
        $month = now()->month;

        return $table
            ->query(
                SalesTarget::query()
                    ->with(['outlet', 'user'])
                    ->where('year', $year)
                    ->where('month', $month)
            )
            ->columns([
                TextColumn::make('outlet.name')
                    ->label('Outlet'),

                TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->default('Semua'),

                TextColumn::make('target_amount')
                    ->label('Target')
                    ->money('IDR'),

                TextColumn::make('actual')
                    ->label('Aktual')
                    ->money('IDR')
                    ->state(function (SalesTarget $record): float {
                        $query = Order::query()
                            ->where('order_status', 'completed')
                            ->whereYear('created_at', $record->year)
                            ->whereMonth('created_at', $record->month);

                        if ($record->user_id) {
                            $query->where('user_id', $record->user_id);
                        } else {
                            $query->where('outlet_id', $record->outlet_id);
                        }

                        return $query->sum('total_amount');
                    }),

                TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function (SalesTarget $record): string {
                        $query = Order::query()
                            ->where('order_status', 'completed')
                            ->whereYear('created_at', $record->year)
                            ->whereMonth('created_at', $record->month);

                        if ($record->user_id) {
                            $query->where('user_id', $record->user_id);
                        } else {
                            $query->where('outlet_id', $record->outlet_id);
                        }

                        $actual = $query->sum('total_amount');
                        $percent = $record->target_amount > 0
                            ? round(($actual / $record->target_amount) * 100, 1)
                            : 0;

                        return "{$percent}% (Rp " . number_format($actual, 0, ',', '.') . ')';
                    })
                    ->color(function (SalesTarget $record): string {
                        $query = Order::query()
                            ->where('order_status', 'completed')
                            ->whereYear('created_at', $record->year)
                            ->whereMonth('created_at', $record->month);

                        if ($record->user_id) {
                            $query->where('user_id', $record->user_id);
                        } else {
                            $query->where('outlet_id', $record->outlet_id);
                        }

                        $actual = $query->sum('total_amount');
                        $percent = $record->target_amount > 0
                            ? round(($actual / $record->target_amount) * 100, 1)
                            : 0;

                        return $percent >= 100 ? 'success' : ($percent >= 50 ? 'warning' : 'danger');
                    }),
            ])
            ->heading('Progress Target Penjualan Bulan Ini');
    }
}
