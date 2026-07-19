<?php

namespace App\Filament\Resources\Expenses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable(),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'operasional' => 'Operasional',
                        'utilities' => 'Utilities',
                        'sewa' => 'Sewa',
                        'gaji' => 'Gaji',
                        'marketing' => 'Marketing',
                        'maintenance' => 'Maintenance',
                        'lainnya' => 'Lainnya',
                        default => $state,
                    })
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('reference_number')
                    ->label('Nomor Referensi')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('outlet')
                    ->relationship('outlet', 'name')
                    ->label('Outlet'),

                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'operasional' => 'Operasional',
                        'utilities' => 'Utilities',
                        'sewa' => 'Sewa',
                        'gaji' => 'Gaji',
                        'marketing' => 'Marketing',
                        'maintenance' => 'Maintenance',
                        'lainnya' => 'Lainnya',
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
