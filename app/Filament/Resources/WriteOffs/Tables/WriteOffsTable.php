<?php

namespace App\Filament\Resources\WriteOffs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WriteOffsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('writeoff_number')
                    ->label('No. Write-Off')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('unit_cost')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('total_loss')
                    ->label('Total Kerugian')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Alasan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'expired' => 'danger',
                        'damaged' => 'orange',
                        'loss' => 'warning',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'expired' => 'Kadaluarsa',
                        'damaged' => 'Rusak',
                        'loss' => 'Hilang',
                        'other' => 'Lainnya',
                        default => $state,
                    }),

                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('reason')
                    ->label('Alasan')
                    ->options([
                        'expired' => 'Kadaluarsa',
                        'damaged' => 'Rusak',
                        'loss' => 'Hilang',
                        'other' => 'Lainnya',
                    ]),

                SelectFilter::make('outlet')
                    ->relationship('outlet', 'name')
                    ->label('Outlet'),

                SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->label('Produk'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
