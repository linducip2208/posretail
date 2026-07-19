<?php

namespace App\Filament\Resources\GiftCards\Tables;

use App\Models\GiftCard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction as TableEditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class GiftCardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->label('Kode'),
                TextColumn::make('type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'nominal' => 'Nominal',
                        'discount_percent' => 'Diskon %',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nominal' => 'info',
                        'discount_percent' => 'warning',
                        default => 'gray',
                    })
                    ->label('Tipe'),
                TextColumn::make('value')
                    ->formatStateUsing(function ($record, $state) {
                        if ($record->type === 'discount_percent') {
                            return $state . '%';
                        }
                        return 'Rp ' . number_format((float) $state, 2, ',', '.');
                    })
                    ->sortable()
                    ->label('Nilai'),
                TextColumn::make('remaining_balance')
                    ->money('IDR')
                    ->sortable()
                    ->visible(fn () => true)
                    ->label('Sisa Saldo'),
                TextColumn::make('valid_from')
                    ->date()
                    ->sortable()
                    ->label('Berlaku Dari'),
                TextColumn::make('valid_until')
                    ->date()
                    ->sortable()
                    ->label('Berlaku Sampai'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'used' => 'info',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->label('Status'),
                TextColumn::make('used_count')
                    ->suffix(fn ($record) => ' / ' . $record->max_usage)
                    ->label('Penggunaan'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Dibuat'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'nominal' => 'Nominal',
                        'discount_percent' => 'Diskon %',
                    ])
                    ->label('Tipe'),
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'used' => 'Terpakai',
                        'expired' => 'Kadaluarsa',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->label('Status'),
            ])
            ->recordActions([
                TableEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('expireCards')
                        ->label('Tandai Kadaluarsa')
                        ->icon('heroicon-o-clock')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->update(['status' => 'expired']);
                            }
                        }),
                ]),
            ]);
    }
}
