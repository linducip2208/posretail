<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExpenseForm
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

                Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'operasional' => 'Operasional',
                        'utilities' => 'Utilities',
                        'sewa' => 'Sewa',
                        'gaji' => 'Gaji',
                        'marketing' => 'Marketing',
                        'maintenance' => 'Maintenance',
                        'lainnya' => 'Lainnya',
                    ])
                    ->required(),

                TextInput::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                DatePicker::make('expense_date')
                    ->label('Tanggal Pengeluaran')
                    ->default(now())
                    ->required(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('reference_number')
                    ->label('Nomor Referensi'),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
