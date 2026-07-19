<?php

namespace App\Filament\Resources\WriteOffs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WriteOffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->inlineLabel()
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Produk')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('unit_cost', \App\Models\Product::find($state)?->cost_price ?? 0)),

                Select::make('product_variant_id')
                    ->relationship('productVariant', 'name')
                    ->label('Varian')
                    ->nullable()
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
                    ->default(1)
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => $set('total_loss', (float) ($get('unit_cost') ?? 0) * (int) $state)),

                TextInput::make('unit_cost')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => $set('total_loss', (float) $state * (int) ($get('quantity') ?? 0))),

                TextInput::make('total_loss')
                    ->label('Total Kerugian')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(true),

                Select::make('reason')
                    ->label('Alasan')
                    ->required()
                    ->options([
                        'expired' => 'Kadaluarsa',
                        'damaged' => 'Rusak',
                        'loss' => 'Hilang',
                        'other' => 'Lainnya',
                    ]),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
