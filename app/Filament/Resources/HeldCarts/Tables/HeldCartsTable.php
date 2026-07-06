<?php

namespace App\Filament\Resources\HeldCarts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HeldCartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->searchable()
                    ->placeholder('Tanpa label'),
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Kasir'),
                TextColumn::make('customer.name')
                    ->label('Pelanggan'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->label('Outlet'),
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
