<?php

namespace App\Filament\Resources\DiscountTemplates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DiscountTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options([
                        'percent' => 'Percent',
                        'fixed' => 'Fixed',
                        'buy_x_get_y' => 'Buy X Get Y',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('min_purchase')
                    ->numeric()
                    ->default(0),
                TextInput::make('buy_quantity')
                    ->numeric()
                    ->default(1)
                    ->visible(fn ($get) => $get('type') === 'buy_x_get_y'),
                TextInput::make('get_quantity')
                    ->numeric()
                    ->default(1)
                    ->visible(fn ($get) => $get('type') === 'buy_x_get_y'),
                DatePicker::make('start_date'),
                DatePicker::make('end_date'),
                Toggle::make('active')
                    ->default(true),
            ]);
    }
}
