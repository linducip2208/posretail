<?php

namespace App\Filament\Resources\CustomerDeposits\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerDepositForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->inlineLabel()
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->label('Pelanggan')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->label('Outlet')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'topup' => 'Topup',
                        'deduct' => 'Potong',
                        'refund' => 'Refund',
                    ])
                    ->required(),

                TextInput::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                TextInput::make('reference')
                    ->label('Referensi'),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
