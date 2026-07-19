<?php

namespace App\Filament\Resources\AssemblyOrders\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssemblyOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('assembly_number')
                    ->label('No. Produksi')
                    ->disabled()
                    ->dehydrated(true)
                    ->required(),
                Select::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required(),
                TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(1),
                Select::make('outlet_id')
                    ->label('Outlet')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id'))
                    ->required(),
                Select::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->required(),
                Repeater::make('items')
                    ->label('Bahan Baku')
                    ->relationship('items')
                    ->schema([
                        Select::make('raw_material_id')
                            ->label('Bahan Baku')
                            ->relationship('rawMaterial', 'name')
                            ->searchable()
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                    ])
                    ->addActionLabel('Tambah Bahan Baku')
                    ->columns(2)
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
