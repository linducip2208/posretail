<?php

namespace App\Filament\Resources\Budgets\Tables;

use App\Models\Budget;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BudgetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('year')
                    ->sortable()
                    ->label('Tahun'),
                TextColumn::make('month')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                        4 => 'April', 5 => 'Mei', 6 => 'Juni',
                        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                        default => (string) $state,
                    })
                    ->label('Bulan'),
                TextColumn::make('outlet.name')
                    ->searchable()
                    ->label('Outlet'),
                TextColumn::make('revenue_target')
                    ->money('IDR')
                    ->sortable()
                    ->label('Target Pendapatan'),
                TextColumn::make('expense_limit')
                    ->money('IDR')
                    ->sortable()
                    ->label('Batas Pengeluaran'),
                TextColumn::make('actual_revenue')
                    ->money('IDR')
                    ->sortable()
                    ->label('Pendapatan Aktual'),
                TextColumn::make('actual_expense')
                    ->money('IDR')
                    ->sortable()
                    ->label('Pengeluaran Aktual'),
                TextColumn::make('revenue_achievement')
                    ->state(function (Budget $record): string {
                        if ($record->revenue_target <= 0) {
                            return '0%';
                        }
                        return round($record->actual_revenue / $record->revenue_target * 100, 1) . '%';
                    })
                    ->label('Capaian Pendapatan')
                    ->color(fn (Budget $record): string => match (true) {
                        $record->revenue_target <= 0 => 'gray',
                        $record->actual_revenue >= $record->revenue_target => 'success',
                        $record->actual_revenue >= $record->revenue_target * 0.8 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('expense_usage')
                    ->state(function (Budget $record): string {
                        if ($record->expense_limit <= 0) {
                            return '0%';
                        }
                        return round($record->actual_expense / $record->expense_limit * 100, 1) . '%';
                    })
                    ->label('Pemakaian Pengeluaran')
                    ->color(fn (Budget $record): string => match (true) {
                        $record->expense_limit <= 0 => 'gray',
                        $record->actual_expense <= $record->expense_limit * 0.8 => 'success',
                        $record->actual_expense <= $record->expense_limit => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('variance')
                    ->state(function (Budget $record): string {
                        $variance = $record->actual_revenue - $record->actual_expense;
                        return number_format($variance, 0, ',', '.');
                    })
                    ->color(fn (Budget $record): string => $record->actual_revenue - $record->actual_expense >= 0 ? 'success' : 'danger')
                    ->label('Selisih'),
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
                SelectFilter::make('year')
                    ->options(function () {
                        $years = Budget::query()->select('year')->distinct()->pluck('year', 'year')->toArray();
                        return $years;
                    })
                    ->label('Tahun'),
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
