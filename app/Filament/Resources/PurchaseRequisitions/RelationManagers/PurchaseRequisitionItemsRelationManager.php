<?php

namespace App\Filament\Resources\PurchaseRequisitions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;

class PurchaseRequisitionItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Item Permintaan';

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->searchable()
                    ->label('Produk'),
                TextColumn::make('quantity')
                    ->sortable()
                    ->label('Jumlah'),
                TextColumn::make('current_stock_snapshot')
                    ->sortable()
                    ->label('Stok Saat Ini'),
                TextColumn::make('reason')
                    ->label('Alasan'),
            ]);
    }
}
