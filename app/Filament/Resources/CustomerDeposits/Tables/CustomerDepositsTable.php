<?php

namespace App\Filament\Resources\CustomerDeposits\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomerDepositsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable(),

                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'topup' => 'success',
                        'deduct' => 'danger',
                        'refund' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'topup' => 'Topup',
                        'deduct' => 'Potong',
                        'refund' => 'Refund',
                        default => $state,
                    }),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('balance_after')
                    ->label('Saldo Setelah')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('reference')
                    ->label('Referensi')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('outlet')
                    ->relationship('outlet', 'name')
                    ->label('Outlet'),

                SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->label('Pelanggan'),

                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'topup' => 'Topup',
                        'deduct' => 'Potong',
                        'refund' => 'Refund',
                    ]),
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
