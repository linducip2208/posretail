<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class WarehouseStockWidget extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        return auth()->user()?->role === 'gudang';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseOrder::query()
                    ->with(['supplier', 'outlet'])
                    ->whereIn('status', ['draft', 'ordered'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('po_number')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier'),

                TextColumn::make('outlet.name')
                    ->label('Outlet Tujuan'),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ordered' => 'warning',
                        'draft' => 'gray',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->heading('Purchase Order Menunggu')
            ->defaultSort('created_at', 'desc');
    }
}
