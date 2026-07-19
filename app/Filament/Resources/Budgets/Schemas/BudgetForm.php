<?php

namespace App\Filament\Resources\Budgets\Schemas;

use App\Models\Outlet;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BudgetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->required()
                    ->label('Outlet'),
                TextInput::make('year')
                    ->required()
                    ->numeric()
                    ->minValue(2020)
                    ->maxValue(2099)
                    ->default(now()->year)
                    ->label('Tahun'),
                Select::make('month')
                    ->options([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                        4 => 'April', 5 => 'Mei', 6 => 'Juni',
                        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                    ])
                    ->required()
                    ->default(now()->month)
                    ->label('Bulan'),
                TextInput::make('revenue_target')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->label('Target Pendapatan'),
                TextInput::make('expense_limit')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->label('Batas Pengeluaran'),
            ]);
    }
}
