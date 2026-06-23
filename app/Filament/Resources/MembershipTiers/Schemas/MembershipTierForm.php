<?php

namespace App\Filament\Resources\MembershipTiers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MembershipTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('min_spent')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('min_orders')
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_percent')
                    ->numeric()
                    ->default(0)
                    ->suffix('%'),
                TextInput::make('point_multiplier')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Toggle::make('active')
                    ->default(true),
            ]);
    }
}
