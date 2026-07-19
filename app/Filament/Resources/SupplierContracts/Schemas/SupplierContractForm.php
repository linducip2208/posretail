<?php

namespace App\Filament\Resources\SupplierContracts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierContractForm
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

                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),

                DatePicker::make('end_date')
                    ->label('Tanggal Berakhir')
                    ->required(),

                TextInput::make('value')
                    ->label('Nilai Kontrak')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->prefix('Rp')
                    ->minValue(0),

                Select::make('payment_terms')
                    ->label('Ketentuan Pembayaran')
                    ->options([
                        'Net 30' => 'Net 30',
                        'COD' => 'COD',
                        'Net 15' => 'Net 15',
                        'Net 60' => 'Net 60',
                    ])
                    ->searchable(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'expired' => 'Kadaluarsa',
                        'terminated' => 'Dihentikan',
                    ])
                    ->required()
                    ->default('active'),

                Textarea::make('terms')
                    ->label('Syarat & Ketentuan')
                    ->columnSpanFull(),
            ]);
    }
}
