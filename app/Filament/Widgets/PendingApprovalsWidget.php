<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Services\ApprovalService;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingApprovalsWidget extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Menunggu Approval';

    protected ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'admin']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user', 'outlet', 'customer'])
                    ->where('order_status', 'pending_approval')
                    ->latest()
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('No. Order')
                    ->searchable(),

                TextColumn::make('outlet.name')
                    ->label('Outlet'),

                TextColumn::make('user.name')
                    ->label('Kasir'),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Order $record): void {
                        app(ApprovalService::class)->approve($record, auth()->id());
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Setujui pesanan ini?')
                    ->modalDescription(fn (Order $record) => "Total: Rp " . number_format($record->total_amount, 0, ',', '.')),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        app(ApprovalService::class)->reject($record, auth()->id(), $data['reason']);
                    })
                    ->requiresConfirmation(),
            ])
            ->emptyStateHeading('Tidak ada pesanan menunggu approval')
            ->emptyStateDescription('Semua pesanan sudah diproses.');
    }
}
