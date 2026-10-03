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
            ->columns([
                TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_type')
                    ->label('Tipe')
                    ->badge()
                    ->sortable(),
                TextColumn::make('queue_number')
                    ->label('Antrian')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('outlet.name')
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
                    ->numeric()
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->numeric()
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tax_amount')
                    ->numeric()
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('deposit_amount')
                    ->label('DP')
                    ->numeric()
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('order_status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
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
