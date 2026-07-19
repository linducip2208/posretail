<?php

namespace App\Filament\Resources\VolumePricings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VolumePricingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->searchable()
                    ->label('Produk'),
                TextColumn::make('customerGroup.name')
                    ->searchable()
                    ->default('Semua')
                    ->label('Grup Pelanggan'),
                TextColumn::make('min_qty')
                    ->sortable()
                    ->label('Min. Qty'),
                TextColumn::make('max_qty')
                    ->sortable()
                    ->default('-')
                    ->label('Max. Qty'),
                TextColumn::make('unit_price')
                    ->money('IDR')
                    ->sortable()
                    ->label('Harga Satuan'),
                TextColumn::make('discount_type')
                    ->formatStateUsing(fn (string $state): string => $state === 'percent' ? 'Persen' : 'Nominal')
                    ->label('Tipe Diskon'),
                TextColumn::make('discount_value')
                    ->formatStateUsing(function ($record, $state) {
                        if ($record->discount_type === 'percent') {
                            return $state . '%';
                        }
                        return 'Rp ' . number_format((float) $state, 2, ',', '.');
                    })
                    ->sortable()
                    ->label('Nilai Diskon'),
                IconColumn::make('active')
                    ->boolean()
                    ->label('Aktif'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Dibuat'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Diperbarui'),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Produk'),
                SelectFilter::make('active')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ])
                    ->label('Status'),
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
}
