<?php

namespace App\Filament\Resources\GiftCards\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GiftCardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options([
                        'nominal' => 'Nominal (Rp)',
                        'discount_percent' => 'Diskon Persen (%)',
                    ])
                    ->required()
                    ->default('nominal')
                    ->live()
                    ->label('Tipe Voucher'),
                TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->label('Nilai'),
                TextInput::make('min_purchase')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('Rp')
                    ->label('Min. Pembelian'),
                DatePicker::make('valid_from')
                    ->required()
                    ->default(now())
                    ->label('Berlaku Dari'),
                DatePicker::make('valid_until')
                    ->required()
                    ->default(now()->addMonths(3))
                    ->rule('after:valid_from')
                    ->label('Berlaku Sampai'),
                TextInput::make('max_usage')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->label('Maks. Penggunaan'),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('Pelanggan'),
            ]);
    }
}
