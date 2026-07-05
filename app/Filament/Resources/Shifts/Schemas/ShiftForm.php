<?php

namespace App\Filament\Resources\Shifts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ShiftForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->label('Kasir'),
                Select::make('outlet_id')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id'))
                    ->required()
                    ->label('Outlet'),
                TextInput::make('starting_cash')
                    ->label('Opening Cash')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                TextInput::make('ending_cash')
                    ->label('Closing Cash')
                    ->numeric()
                    ->prefix('Rp')
                    ->nullable(),
                TextInput::make('expected_cash')
                    ->label('Total Sales')
                    ->numeric()
                    ->prefix('Rp')
                    ->nullable(),
                TextInput::make('difference')
                    ->label('Difference')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),
                DateTimePicker::make('started_at')
                    ->label('Opened At')
                    ->required(),
                DateTimePicker::make('ended_at')
                    ->label('Closed At')
                    ->nullable(),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'closed' => 'Closed',
                    ])
                    ->required()
                    ->default('active'),
                Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull(),
            ]);
    }
}
