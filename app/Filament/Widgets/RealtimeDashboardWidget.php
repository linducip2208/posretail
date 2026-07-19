<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RealtimeDashboardWidget extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '10s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['customer', 'outlet', 'user'])
                    ->orderByDesc('created_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('No. Order')
                    ->searchable(),

                TextColumn::make('outlet.name')
                    ->label('Outlet'),

                TextColumn::make('user.name')
                    ->label('Kasir'),

                TextColumn::make('customer.name')
                    ->label('Pelanggan'),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR'),

                TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'processing' => 'warning',
                        'pending' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since(),
            ])
            ->heading('Pesanan Terbaru (Realtime)')
            ->emptyStateHeading('Belum ada pesanan')
            ->emptyStateDescription('Pesanan baru akan muncul realtime di sini.');
    }

    protected static function isVisibleToRole(?string $role): bool
    {
        return in_array($role, ['owner', 'manager', 'admin', 'kasir']);
    }
}
