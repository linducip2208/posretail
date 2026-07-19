<?php

namespace App\Filament\Resources\SalesTargets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SalesTargetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->inlineLabel()
            ->components([
                Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->label('Outlet')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Karyawan')
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->helperText('Kosongkan untuk target outlet'),

                TextInput::make('year')
                    ->label('Tahun')
                    ->numeric()
                    ->required()
                    ->default(now()->year),

                TextInput::make('month')
                    ->label('Bulan')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(12)
                    ->required()
                    ->default(now()->month),

                TextInput::make('target_amount')
                    ->label('Target (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
            ]);
    }
}
