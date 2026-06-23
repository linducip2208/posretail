<?php

namespace App\Filament\Resources\Shifts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShiftsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Kasir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('starting_cash')
                    ->label('Opening Cash')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('expected_cash')
                    ->label('Total Sales')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('ending_cash')
                    ->label('Closing Cash')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('difference')
                    ->label('Difference')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($record): string => ($record->difference ?? 0) < 0 ? 'danger' : 'success'),
                TextColumn::make('started_at')
                    ->label('Opened At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->label('Closed At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->label('Outlet'),
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'closed' => 'Closed',
                    ])
                    ->label('Status'),
            ])
            ->defaultSort('started_at', 'desc')
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
