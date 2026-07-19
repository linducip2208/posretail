<?php

namespace App\Filament\Resources\Reservations\Tables;

use App\Models\Reservation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reservation_number')
                    ->label('No. Reservasi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reservation_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('time_slot')
                    ->label('Slot Waktu')
                    ->sortable(),

                TextColumn::make('table.name')
                    ->label('Meja')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable(),

                TextColumn::make('customer_phone')
                    ->label('Telepon')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('guest_count')
                    ->label('Tamu')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'booked' => 'primary',
                        'arrived' => 'success',
                        'cancelled' => 'danger',
                        'no_show' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'booked' => 'Dipesan',
                        'arrived' => 'Hadir',
                        'cancelled' => 'Dibatalkan',
                        'no_show' => 'Tdk Hadir',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'booked' => 'Dipesan',
                        'arrived' => 'Hadir',
                        'cancelled' => 'Dibatalkan',
                        'no_show' => 'Tdk Hadir',
                    ]),

                Filter::make('reservation_date')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $d) => $q->whereDate('reservation_date', '>=', $d))
                            ->when($data['until'], fn (Builder $q, $d) => $q->whereDate('reservation_date', '<=', $d));
                    }),
            ])
            ->recordActions([
                Action::make('arrive')
                    ->label('Hadir')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Reservation $record): bool => $record->status === 'booked')
                    ->action(function (Reservation $record) {
                        $record->update(['status' => 'arrived']);
                        Notification::make()
                            ->title('Pelanggan telah hadir')
                            ->success()
                            ->send();
                    }),

                Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Reservation $record): bool => $record->status === 'booked')
                    ->requiresConfirmation()
                    ->action(function (Reservation $record) {
                        $record->update(['status' => 'cancelled']);
                        Notification::make()
                            ->title('Reservasi dibatalkan')
                            ->success()
                            ->send();
                    }),

                Action::make('noShow')
                    ->label('Tidak Hadir')
                    ->icon('heroicon-o-face-frown')
                    ->color('warning')
                    ->visible(fn (Reservation $record): bool => $record->status === 'booked')
                    ->requiresConfirmation()
                    ->action(function (Reservation $record) {
                        $record->update(['status' => 'no_show']);
                        Notification::make()
                            ->title('Ditandai tidak hadir')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
