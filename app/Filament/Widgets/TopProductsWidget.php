<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = '120s';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'admin']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->join('order_items', 'order_items.product_id', '=', 'products.id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.order_status', 'completed')
                    ->where('orders.created_at', '>=', now()->subDays(7))
                    ->selectRaw('products.id, products.name, products.sku, SUM(order_items.quantity) as sold, SUM(order_items.subtotal) as revenue')
                    ->groupBy('products.id', 'products.name', 'products.sku')
                    ->orderByDesc('sold')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable()
                    ->description(fn ($record): string => $record->sku ?? '-'),

                TextColumn::make('sold')
                    ->label('Terjual')
                    ->numeric()
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('revenue')
                    ->label('Pendapatan')
                    ->money('IDR')
                    ->alignRight()
                    ->sortable(),
            ])
            ->heading('Produk Terlaris (7 Hari)')
            ->paginated(false)
            ->emptyStateHeading('Belum ada penjualan 7 hari terakhir')
            ->emptyStateDescription('Produk terlaris akan tampil di sini setelah ada transaksi.');
    }
}
