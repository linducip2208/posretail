<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Riwayat Pesanan';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable(),
                TextColumn::make('order_type')
                    ->badge(),
                TextColumn::make('total_amount')
                    ->money('IDR'),
                TextColumn::make('payment_status')
                    ->badge(),
                TextColumn::make('order_status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->headerActions([])
            ->actions([]);
    }
}
