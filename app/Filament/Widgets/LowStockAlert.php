<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlert extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with(['category', 'outlet'])
                    ->where('active', true)
                    ->whereColumn('current_stock', '<=', 'min_stock')
                    ->orderBy('current_stock')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable(),

                TextColumn::make('sku')
                    ->label('SKU'),

                TextColumn::make('category.name')
                    ->label('Kategori'),

                TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->sortable()
                    ->color('danger'),

                TextColumn::make('min_stock')
                    ->label('Min. Stok')
                    ->sortable(),
            ])
            ->heading('Peringatan Stok Rendah')
            ->emptyStateHeading('Semua stok aman')
            ->emptyStateDescription('Tidak ada produk dengan stok di bawah batas minimum.');
    }
}
