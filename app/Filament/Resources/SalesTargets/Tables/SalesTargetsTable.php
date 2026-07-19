<?php

namespace App\Filament\Resources\SalesTargets\Tables;

use App\Models\Order;
use App\Models\SalesTarget;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SalesTargetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),

                TextColumn::make('month')
                    ->label('Bulan')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                        4 => 'April', 5 => 'Mei', 6 => 'Juni',
                        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                        default => (string) $state,
                    }),

                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('target_amount')
                    ->label('Target')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('achievement_percent')
                    ->label('Pencapaian')
                    ->state(fn (SalesTarget $record): array => self::computeAchievement($record))
                    ->formatStateUsing(fn (array $state): string => number_format($state['percent'], 1) . '%')
                    ->description(fn (SalesTarget $record): string => 'Rp ' . number_format(self::computeAchievement($record)['actual'], 0, ',', '.'))
                    ->color(fn (SalesTarget $record): string => self::computeAchievement($record)['percent'] >= 100 ? 'success' : 'warning'),
            ])
            ->filters([
                SelectFilter::make('outlet')
                    ->relationship('outlet', 'name')
                    ->label('Outlet'),

                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(fn () => SalesTarget::selectRaw('year')->distinct()->pluck('year', 'year')),

                SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                        4 => 'April', 5 => 'Mei', 6 => 'Juni',
                        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
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

    public static function computeAchievement(SalesTarget $record): array
    {
        $query = Order::query()
            ->where('order_status', 'completed')
            ->whereYear('created_at', $record->year)
            ->whereMonth('created_at', $record->month);

        if ($record->user_id) {
            $query->where('user_id', $record->user_id);
        } else {
            $query->where('outlet_id', $record->outlet_id);
        }

        $actual = $query->sum('total_amount');
        $percent = $record->target_amount > 0
            ? round(($actual / $record->target_amount) * 100, 2)
            : 0;

        return ['actual' => $actual, 'percent' => $percent];
    }
}
