<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('parent_id')
                    ->relationship('parent', 'name'),
                Select::make('outlet_id')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id')),
                Toggle::make('active')
                    ->required(),
            ]);
    }
}
