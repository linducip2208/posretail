<?php

namespace App\Filament\Resources\BinLocations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BinLocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->required()
                    ->label('Outlet'),
                TextInput::make('code')
                    ->required()
                    ->maxLength(50)
                    ->label('Kode'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(200)
                    ->label('Nama'),
                Select::make('zone')
                    ->options([
                        'Depan' => 'Depan',
                        'Belakang' => 'Belakang',
                        'Atas' => 'Atas',
                        'Gudang' => 'Gudang',
                    ])
                    ->required()
                    ->label('Zona'),
                Toggle::make('active')
                    ->default(true)
                    ->label('Aktif'),
            ]);
    }
}
