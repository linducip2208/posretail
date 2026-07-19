<?php

namespace App\Filament\Resources\SupplierRatings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SupplierRatingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->required(),

                Select::make('purchase_order_id')
                    ->label('Purchase Order')
                    ->relationship('purchaseOrder', 'po_number')
                    ->searchable()
                    ->required(),

                Select::make('on_time')
                    ->label('Ketepatan Waktu')
                    ->options([
                        1 => '★ (1) - Sangat Buruk',
                        2 => '★★ (2) - Buruk',
                        3 => '★★★ (3) - Cukup',
                        4 => '★★★★ (4) - Baik',
                        5 => '★★★★★ (5) - Sangat Baik',
                    ])
                    ->required()
                    ->default(5),

                Select::make('quality')
                    ->label('Kualitas')
                    ->options([
                        1 => '★ (1) - Sangat Buruk',
                        2 => '★★ (2) - Buruk',
                        3 => '★★★ (3) - Cukup',
                        4 => '★★★★ (4) - Baik',
                        5 => '★★★★★ (5) - Sangat Baik',
                    ])
                    ->required()
                    ->default(5),

                Select::make('price_competitiveness')
                    ->label('Daya Saing Harga')
                    ->options([
                        1 => '★ (1) - Sangat Buruk',
                        2 => '★★ (2) - Buruk',
                        3 => '★★★ (3) - Cukup',
                        4 => '★★★★ (4) - Baik',
                        5 => '★★★★★ (5) - Sangat Baik',
                    ])
                    ->required()
                    ->default(5),

                Select::make('communication')
                    ->label('Komunikasi')
                    ->options([
                        1 => '★ (1) - Sangat Buruk',
                        2 => '★★ (2) - Buruk',
                        3 => '★★★ (3) - Cukup',
                        4 => '★★★★ (4) - Baik',
                        5 => '★★★★★ (5) - Sangat Baik',
                    ])
                    ->required()
                    ->default(5),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
