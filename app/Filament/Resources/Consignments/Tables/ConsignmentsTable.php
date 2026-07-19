<?php

namespace App\Filament\Resources\Consignments\Tables;

use App\Models\Consignment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConsignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('consignment_number')
                    ->label('No. Konsinyasi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('sold_quantity')
                    ->label('Terjual')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('remaining')
                    ->label('Sisa')
                    ->state(fn (Consignment $record): int => $record->remaining())
                    ->numeric(),

                TextColumn::make('unit_price')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('commission_percent')
                    ->label('Komisi (%)')
                    ->suffix('%'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'settled' => 'info',
                        'returned' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'settled' => 'Selesai',
                        'returned' => 'Dikembalikan',
                        default => $state,
                    }),

                TextColumn::make('start_date')
                    ->label('Tgl Mulai')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'settled' => 'Selesai',
                        'returned' => 'Dikembalikan',
                    ]),

                SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->label('Supplier'),
            ])
            ->recordActions([
                Action::make('settle')
                    ->label('Settlement')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Consignment $record): bool => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(function (Consignment $record) {
                        $record->update([
                            'status' => 'settled',
                            'settlement_date' => today(),
                        ]);
                        Notification::make()
                            ->title('Konsinyasi diselesaikan')
                            ->body('Komisi: Rp ' . number_format($record->commissionAmount(), 0, ',', '.'))
                            ->success()
                            ->send();
                    }),

                Action::make('return')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->visible(fn (Consignment $record): bool => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(function (Consignment $record) {
                        $record->update(['status' => 'returned']);
                        Notification::make()
                            ->title('Barang konsinyasi dikembalikan')
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
