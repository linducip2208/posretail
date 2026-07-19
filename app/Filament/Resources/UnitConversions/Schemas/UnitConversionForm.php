<?php

namespace App\Filament\Resources\UnitConversions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UnitConversionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('from_unit_id')
                    ->label('Dari Satuan')
                    ->relationship('fromUnit', 'name')
                    ->searchable()
                    ->required(),
                Select::make('to_unit_id')
                    ->label('Ke Satuan')
                    ->relationship('toUnit', 'name')
                    ->searchable()
                    ->required(),
                TextInput::make('conversion_rate')
                    ->label('Nilai Konversi')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->helperText('1 dari_satuan = ? ke_satuan'),
                Select::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->nullable()
                    ->helperText('Kosongkan untuk konversi global'),
            ]);
    }
}
