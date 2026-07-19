<?php

namespace App\Filament\Resources\VolumePricings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VolumePricingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Produk'),
                Select::make('customer_group_id')
                    ->relationship('customerGroup', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('Grup Pelanggan'),
                TextInput::make('min_qty')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->label('Min. Qty'),
                TextInput::make('max_qty')
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->label('Max. Qty'),
                TextInput::make('unit_price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('Rp')
                    ->label('Harga Satuan'),
                Select::make('discount_type')
                    ->options([
                        'fixed' => 'Nominal (Rp)',
                        'percent' => 'Persen (%)',
                    ])
                    ->required()
                    ->default('fixed')
                    ->label('Tipe Diskon'),
                TextInput::make('discount_value')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->label('Nilai Diskon'),
                Toggle::make('active')
                    ->default(true)
                    ->label('Aktif'),
            ]);
    }
}
