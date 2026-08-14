<?php

namespace App\Filament\Resources\SupplierReturns\Tables;

use App\Models\SupplierReturn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction as TableEditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupplierReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('return_number')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->label('No. Retur'),
                TextColumn::make('supplier.name')
                    ->searchable()
                    ->label('Supplier'),
                TextColumn::make('purchaseOrder.po_number')
                    ->placeholder('-')
                    ->label('Purchase Order'),
                TextColumn::make('outlet.name')
                    ->label('Outlet'),
                TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable()
                    ->label('Total'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'submitted' => 'Diajukan',
                        'received' => 'Diterima',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->label('Status'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Dibuat'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Diajukan',
                        'received' => 'Diterima',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->label('Status'),
                SelectFilter::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Supplier'),
            ])
            ->recordActions([
                Action::make('receive')
                    ->label('Terima Retur')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (SupplierReturn $record): bool => in_array($record->status, ['draft', 'submitted']))
                    ->action(function (SupplierReturn $record) {
                        $record->load('items');
                        $record->markReceived();

                        Notification::make()
                            ->title('Retur diterima')
                            ->body("Stok dikurangi dan hutang supplier diperbarui untuk {$record->return_number}.")
                            ->success()
                            ->send();
                    }),
                TableEditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
