<?php

namespace App\Filament\Resources\Consignments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ConsignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->inlineLabel()
            ->components([
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->label('Supplier')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Produk')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->label('Outlet')
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->required()
                    ->default(0),

                TextInput::make('unit_price')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                TextInput::make('commission_percent')
                    ->label('Komisi (%)')
                    ->numeric()
                    ->suffix('%')
                    ->default(0),

                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->default(today()),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
