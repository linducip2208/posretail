<?php

namespace App\Filament\Resources\SupplierRatings\Tables;

use App\Models\SupplierRating;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupplierRatingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('purchaseOrder.po_number')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('on_time')
                    ->label('Ketepatan Waktu')
                    ->formatStateUsing(fn (int $state): string => self::renderStars($state))
                    ->html()
                    ->color(fn (int $state): string => self::scoreColor($state)),

                TextColumn::make('quality')
                    ->label('Kualitas')
                    ->formatStateUsing(fn (int $state): string => self::renderStars($state))
                    ->html()
                    ->color(fn (int $state): string => self::scoreColor($state)),

                TextColumn::make('price_competitiveness')
                    ->label('Daya Saing Harga')
                    ->formatStateUsing(fn (int $state): string => self::renderStars($state))
                    ->html()
                    ->color(fn (int $state): string => self::scoreColor($state)),

                TextColumn::make('communication')
                    ->label('Komunikasi')
                    ->formatStateUsing(fn (int $state): string => self::renderStars($state))
                    ->html()
                    ->color(fn (int $state): string => self::scoreColor($state)),

                TextColumn::make('avg_score')
                    ->label('Rata-rata')
                    ->formatStateUsing(fn (float $state): string => self::renderStars((int) round($state)) . ' (' . $state . ')')
                    ->html()
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->label('Supplier'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function renderStars(int $score): string
    {
        $color = self::scoreColor($score);
        $out = '';
        for ($i = 1; $i <= 5; $i++) {
            $out .= $i <= $score ? '<span style="color: ' . $color . '">★</span>' : '<span style="color: #d1d5db">★</span>';
        }
        return $out;
    }

    protected static function scoreColor(int $score): string
    {
        return match (true) {
            $score >= 4 => '#16a34a',
            $score === 3 => '#ca8a04',
            default => '#dc2626',
        };
    }
}
