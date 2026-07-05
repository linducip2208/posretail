<?php

namespace App\Filament\Resources\TableRestos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TableRestoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                Select::make('outlet_id')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id'))
                    ->required(),
                Select::make('table_area_id')
                    ->relationship('tableArea', 'name'),
                TextInput::make('capacity')
                    ->required()
                    ->numeric()
                    ->default(4),
                Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'reserved' => 'Reserved',
                        'maintenance' => 'Maintenance',
                    ])
                    ->default('available')
                    ->required(),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Toggle::make('active')
                    ->default(true),
            ]);
    }
}
