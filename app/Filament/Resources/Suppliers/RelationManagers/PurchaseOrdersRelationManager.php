<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseOrders';

    protected static ?string $title = 'Riwayat PO';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('po_number')
                    ->searchable(),
                TextColumn::make('outlet.name'),
                TextColumn::make('total_amount')
                    ->money('IDR'),
                TextColumn::make('status'),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->headerActions([])
            ->actions([]);
    }
}
