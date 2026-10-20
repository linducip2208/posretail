<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('brand.name')
                    ->label('Merek')
                    ->searchable(),
                TextColumn::make('unit.name')
                    ->label('Satuan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('barcode')
                    ->label('Barcode')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('barcode_image')
                    ->getStateUsing(fn ($record) => $record->barcode ? route('barcode.image', ['code' => $record->barcode, 'width' => 1, 'height' => 25]) : null)
                    ->label('Barcode')
                    ->size(120)
                    ->defaultImageUrl(null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cost_price')
                    ->label('Modal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('selling_price')
                    ->label('Jual')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('wholesale_price')
                    ->label('Grosir')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('member_price')
                    ->label('Member')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('min_stock')
                    ->label('Min')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_stock')
                    ->label('Maks')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('current_stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('image')
                    ->label('Foto')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('has_variants')
                    ->label('Varian')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
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
                TrashedFilter::make(),
            ])
            ->recordActions([
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
