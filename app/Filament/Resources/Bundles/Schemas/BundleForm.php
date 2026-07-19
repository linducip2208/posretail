<?php

namespace App\Filament\Resources\Bundles\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BundleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                TextInput::make('bundle_price')
                    ->label('Harga Bundle')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->default(0),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                Toggle::make('active')
                    ->label('Aktif')
                    ->default(true),
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai'),
                DatePicker::make('end_date')
                    ->label('Tanggal Berakhir'),
                Repeater::make('items')
                    ->label('Item Bundle')
                    ->relationship('items')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->required()
                            ->live(),
                        Select::make('product_variant_id')
                            ->label('Varian')
                            ->relationship('variant', 'name')
                            ->searchable()
                            ->nullable(),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1),
                    ])
                    ->addActionLabel('Tambah Item')
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
