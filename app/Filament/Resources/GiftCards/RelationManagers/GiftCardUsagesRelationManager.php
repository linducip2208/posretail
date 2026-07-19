<?php

namespace App\Filament\Resources\GiftCards\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GiftCardUsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'usages';

    protected static ?string $title = 'Riwayat Pemakaian';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->searchable()
                    ->label('No. Pesanan'),
                TextColumn::make('amount_used')
                    ->money('IDR')
                    ->label('Nilai Digunakan'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Tanggal'),
            ])
            ->headerActions([])
            ->actions([]);
    }
}
