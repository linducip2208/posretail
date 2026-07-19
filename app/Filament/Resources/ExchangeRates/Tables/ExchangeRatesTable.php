<?php

namespace App\Filament\Resources\ExchangeRates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExchangeRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('currency')
                    ->searchable()
                    ->sortable()
                    ->label('Mata Uang'),
                TextColumn::make('rate')
                    ->numeric(4)
                    ->sortable()
                    ->label('Kurs'),
                TextColumn::make('effective_date')
                    ->date()
                    ->sortable()
                    ->label('Tanggal Berlaku'),
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
                SelectFilter::make('currency')
                    ->options([
                        'USD' => 'USD',
                        'SGD' => 'SGD',
                        'MYR' => 'MYR',
                        'EUR' => 'EUR',
                        'GBP' => 'GBP',
                        'JPY' => 'JPY',
                        'AUD' => 'AUD',
                        'CNY' => 'CNY',
                        'HKD' => 'HKD',
                        'THB' => 'THB',
                    ])
                    ->label('Mata Uang'),
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
