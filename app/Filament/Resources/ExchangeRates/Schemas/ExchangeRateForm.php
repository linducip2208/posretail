<?php

namespace App\Filament\Resources\ExchangeRates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExchangeRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('currency')
                    ->options([
                        'USD' => 'USD - US Dollar',
                        'SGD' => 'SGD - Singapore Dollar',
                        'MYR' => 'MYR - Malaysian Ringgit',
                        'EUR' => 'EUR - Euro',
                        'GBP' => 'GBP - British Pound',
                        'JPY' => 'JPY - Japanese Yen',
                        'AUD' => 'AUD - Australian Dollar',
                        'CNY' => 'CNY - Chinese Yuan',
                        'HKD' => 'HKD - Hong Kong Dollar',
                        'THB' => 'THB - Thai Baht',
                    ])
                    ->required()
                    ->searchable()
                    ->label('Mata Uang'),
                TextInput::make('rate')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.0001)
                    ->label('Kurs (terhadap IDR)'),
                DatePicker::make('effective_date')
                    ->required()
                    ->default(now())
                    ->label('Tanggal Berlaku'),
            ]);
    }
}
