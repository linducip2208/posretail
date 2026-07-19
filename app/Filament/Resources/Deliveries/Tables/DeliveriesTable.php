<?php

namespace App\Filament\Resources\Deliveries\Tables;

use App\Models\Delivery;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DeliveriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('delivery_number')
                    ->label('No. Pengiriman')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.order_number')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('recipient_name')
                    ->label('Penerima')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'packed' => 'info',
                        'shipped' => 'warning',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'packed' => 'Dikemas',
                        'shipped' => 'Dikirim',
                        'delivered' => 'Terkirim',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('tracking_number')
                    ->label('No. Resi')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'packed' => 'Dikemas',
                        'shipped' => 'Dikirim',
                        'delivered' => 'Terkirim',
                        'cancelled' => 'Dibatalkan',
                    ]),

                SelectFilter::make('driver')
                    ->relationship('driver', 'name')
                    ->label('Driver'),
            ])
            ->recordActions([
                Action::make('pack')
                    ->label('Kemas')
                    ->icon('heroicon-o-archive-box')
                    ->color('info')
                    ->visible(fn (Delivery $record): bool => $record->status === 'pending')
                    ->action(function (Delivery $record) {
                        $record->update(['status' => 'packed', 'packed_at' => now()]);
                        Notification::make()
                            ->title('Pesanan dikemas')
                            ->success()
                            ->send();
                    }),

                Action::make('ship')
                    ->label('Kirim')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn (Delivery $record): bool => $record->status === 'packed')
                    ->action(function (Delivery $record) {
                        $record->update(['status' => 'shipped', 'shipped_at' => now()]);
                        Notification::make()
                            ->title('Pesanan dikirim')
                            ->success()
                            ->send();
                    }),

                Action::make('deliver')
                    ->label('Terkirim')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Delivery $record): bool => $record->status === 'shipped')
                    ->action(function (Delivery $record) {
                        $record->update(['status' => 'delivered', 'delivered_at' => now()]);
                        Notification::make()
                            ->title('Pesanan terkirim')
                            ->success()
                            ->send();
                    }),

                Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Delivery $record): bool => !in_array($record->status, ['delivered', 'cancelled']))
                    ->requiresConfirmation()
                    ->action(function (Delivery $record) {
                        $record->update(['status' => 'cancelled']);
                        Notification::make()
                            ->title('Pengiriman dibatalkan')
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
