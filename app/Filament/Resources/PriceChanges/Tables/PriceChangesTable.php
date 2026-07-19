<?php

namespace App\Filament\Resources\PriceChanges\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PriceChangesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('old_cost_price')
                    ->label('Harga Beli Lama')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('new_cost_price')
                    ->label('Harga Beli Baru')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('old_selling_price')
                    ->label('Harga Jual Lama')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('new_selling_price')
                    ->label('Harga Jual Baru')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('changed_fields')
                    ->label('Field Berubah')
                    ->badge()
                    ->sortable(),
                TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}
