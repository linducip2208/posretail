<?php

namespace App\Filament\Resources\StockTransfers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('transfer_number')
                    ->label('No. Transfer')
                    ->searchable(),
                TextColumn::make('fromOutlet.name')
                    ->label('Asal')
                    ->searchable(),
                TextColumn::make('toOutlet.name')
                    ->label('Tujuan')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('from_outlet_id')
                    ->relationship('fromOutlet', 'name')
                    ->label('Outlet Asal'),
                SelectFilter::make('to_outlet_id')
                    ->relationship('toOutlet', 'name')
                    ->label('Outlet Tujuan'),
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
