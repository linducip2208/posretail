<?php

namespace App\Filament\Resources\SerialNumbers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction as TableEditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SerialNumbersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('serial_number')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->label('Nomor Seri / IMEI'),
                TextColumn::make('product.name')
                    ->searchable()
                    ->label('Produk'),
                TextColumn::make('productVariant.name')
                    ->placeholder('-')
                    ->label('Varian'),
                TextColumn::make('outlet.name')
                    ->placeholder('-')
                    ->label('Outlet'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_stock' => 'success',
                        'sold' => 'gray',
                        'returned' => 'warning',
                        'defective' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_stock' => 'Stok Tersedia',
                        'sold' => 'Terjual',
                        'returned' => 'Retur',
                        'defective' => 'Rusak',
                        default => $state,
                    })
                    ->label('Status'),
                TextColumn::make('orderItem.order.order_number')
                    ->placeholder('-')
                    ->searchable()
                    ->label('No. Pesanan'),
                TextColumn::make('warranty_expires_at')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->label('Garansi Sampai')
                    ->color(fn ($record) => match ($record->warrantyStatus()) {
                        'expired' => 'danger',
                        'expiring' => 'warning',
                        'active' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Diperbarui'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'in_stock' => 'Stok Tersedia',
                        'sold' => 'Terjual',
                        'returned' => 'Retur',
                        'defective' => 'Rusak',
                    ])
                    ->label('Status'),
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Produk'),
            ])
            ->recordActions([
                TableEditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
