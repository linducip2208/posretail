<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringProductsWidget extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    protected static function isVisibleToRole(?string $role): bool
    {
        return in_array($role, ['owner', 'manager', 'admin', 'gudang'], true);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with(['category', 'outlet'])
                    ->where('active', true)
                    ->whereNotNull('expired_date')
                    ->where('current_stock', '>', 0)
                    ->where('expired_date', '<=', now()->addDays(30))
                    ->orderBy('expired_date')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable(),

                TextColumn::make('batch_number')
                    ->placeholder('-')
                    ->fontFamily('mono')
                    ->label('Batch'),

                TextColumn::make('category.name')
                    ->label('Kategori'),

                TextColumn::make('current_stock')
                    ->label('Stok')
                    ->sortable(),

                TextColumn::make('expired_date')
                    ->date('d/m/Y')
                    ->label('Expired')
                    ->sortable()
                    ->color(fn ($record) => $record->expired_date->isPast() ? 'danger' : 'warning')
                    ->description(fn ($record) => $record->expired_date->isPast()
                        ? 'Sudah expired'
                        : ($record->expired_date->diffInDays(now()).' hari lagi')),
            ])
            ->heading('Produk Mendekati Kadaluarsa (FEFO)')
            ->emptyStateHeading('Tidak ada produk mendekati kadaluarsa')
            ->emptyStateDescription('Semua produk masih dalam masa berlaku.');
    }
}
