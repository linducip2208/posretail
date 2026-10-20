<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Services\TaxInvoiceService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('No. Order')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_type')
                    ->label('Tipe')
                    ->badge()
                    ->sortable(),
                TextColumn::make('queue_number')
                    ->label('Antrian')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Kasir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Pegawai')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->numeric()
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tax_amount')
                    ->label('Pajak')
                    ->numeric()
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->numeric()
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('deposit_amount')
                    ->label('DP')
                    ->numeric()
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->badge()
                    ->sortable(),
                TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->label('Outlet'),
                SelectFilter::make('order_type')
                    ->options([
                        'walk_in' => 'Walk-in',
                        'member' => 'Member',
                        'takeaway' => 'Takeaway',
                        'delivery' => 'Delivery',
                        'online' => 'Online',
                    ]),
                SelectFilter::make('order_status'),
                SelectFilter::make('payment_status'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('generateTaxInvoice')
                    ->label('Buat e-Faktur')
                    ->icon('tabler-file-description')
                    ->color('success')
                    ->visible(fn ($record) => $record->order_status === 'completed' && ! $record->taxInvoices()->exists())
                    ->action(function ($record) {
                        TaxInvoiceService::generateFromOrder($record);

                        Notification::make()
                            ->title('e-Faktur dibuat')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
