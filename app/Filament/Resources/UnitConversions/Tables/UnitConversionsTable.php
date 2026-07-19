<?php

namespace App\Filament\Resources\UnitConversions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UnitConversionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fromUnit.name')
                    ->label('Dari Satuan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('toUnit.name')
                    ->label('Ke Satuan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('conversion_rate')
                    ->label('Nilai Konversi')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Global'),
                TextColumn::make('product_id')
                    ->label('Cakupan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Per Produk' : 'Global')
                    ->color(fn ($state) => $state ? 'warning' : 'success'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
