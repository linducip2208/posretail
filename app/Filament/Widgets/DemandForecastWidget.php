<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class DemandForecastWidget extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '300s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->where('orders.order_status', 'completed')
                    ->where('orders.created_at', '>=', now()->subWeeks(12))
                    ->select(
                        'products.id',
                        'products.name as product_name',
                        'products.current_stock',
                        DB::raw('ROUND(SUM(order_items.quantity) / 12, 1) as avg_weekly_sold'),
                        DB::raw('CEIL(ROUND(SUM(order_items.quantity) / 12, 1) * 1.1) as forecast'),
                        DB::raw('GREATEST(0, CEIL(ROUND(SUM(order_items.quantity) / 12, 1) * 1.1) - products.current_stock) as suggested_order')
                    )
                    ->groupBy('products.id', 'products.name', 'products.current_stock')
                    ->orderByDesc('avg_weekly_sold')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('product_name')
                    ->label('Produk')
                    ->searchable(),

                TextColumn::make('avg_weekly_sold')
                    ->label('Rata-rata Mingguan')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('forecast')
                    ->label('Perkiraan Minggu Depan')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('suggested_order')
                    ->label('Saran Order')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('suggested_order')
                    ->label('Status')
                    ->formatStateUsing(fn ($state): string => $state > 0 ? 'Pesan Segera' : 'Aman')
                    ->badge()
                    ->color(fn ($state): string => $state > 0 ? 'danger' : 'success'),
            ])
            ->heading('Perkiraan Permintaan (12 Minggu)')
            ->emptyStateHeading('Belum cukup data')
            ->emptyStateDescription('Butuh minimal 4 minggu data penjualan untuk perkiraan.');
    }

    protected static function isVisibleToRole(?string $role): bool
    {
        return in_array($role, ['owner', 'manager', 'admin']);
    }
}
