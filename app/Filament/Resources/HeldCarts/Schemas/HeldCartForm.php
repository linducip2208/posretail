<?php

namespace App\Filament\Resources\HeldCarts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HeldCartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->maxLength(255),
                Select::make('outlet_id')
                    ->label('Outlet')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id'))
                    ->required(),
                Select::make('user_id')
                    ->label('Kasir')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('customer_id')
                    ->label('Pelanggan')
                    ->relationship('customer', 'name'),
            ]);
    }
}
