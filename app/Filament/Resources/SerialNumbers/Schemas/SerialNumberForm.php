<?php

namespace App\Filament\Resources\SerialNumbers\Schemas;

use App\Models\ProductVariant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SerialNumberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('product_variant_id', null))
                    ->label('Produk'),
                Select::make('product_variant_id')
                    ->options(fn (Get $get) => ProductVariant::query()
                        ->where('product_id', $get('product_id'))
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->nullable()
                    ->label('Varian'),
                Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('Outlet'),
                TextInput::make('serial_number')
                    ->required()
                    ->maxLength(100)
                    ->label('Nomor Seri / IMEI'),
                Select::make('status')
                    ->options([
                        'in_stock' => 'Stok Tersedia',
                        'sold' => 'Terjual',
                        'returned' => 'Retur',
                        'defective' => 'Rusak',
                    ])
                    ->required()
                    ->default('in_stock')
                    ->label('Status'),
                Textarea::make('notes')
                    ->rows(3)
                    ->label('Catatan'),
            ]);
    }
}
