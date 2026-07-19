<?php

namespace App\Filament\Resources\BinLocations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BinLocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->label('Kode'),
                TextColumn::make('name')
                    ->searchable()
                    ->label('Nama'),
                TextColumn::make('zone')
                    ->sortable()
                    ->label('Zona'),
                TextColumn::make('outlet.name')
                    ->searchable()
                    ->label('Outlet'),
                IconColumn::make('active')
                    ->boolean()
                    ->sortable()
                    ->label('Aktif'),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->sortable()
                    ->label('Jumlah Produk'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Dibuat Pada'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Diperbarui Pada'),
            ])
            ->filters([
                SelectFilter::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->label('Outlet'),
                SelectFilter::make('zone')
                    ->options([
                        'Depan' => 'Depan',
                        'Belakang' => 'Belakang',
                        'Atas' => 'Atas',
                        'Gudang' => 'Gudang',
                    ])
                    ->label('Zona'),
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
