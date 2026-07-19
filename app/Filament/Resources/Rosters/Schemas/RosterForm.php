<?php

namespace App\Filament\Resources\Rosters\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class RosterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('outlet_id')
                    ->label('Outlet')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id'))
                    ->required(),
                Select::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Select::make('day_of_week')
                    ->label('Hari')
                    ->options([
                        0 => 'Minggu',
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                    ])
                    ->required(),
                TimePicker::make('shift_start')
                    ->label('Jam Masuk')
                    ->required(),
                TimePicker::make('shift_end')
                    ->label('Jam Pulang')
                    ->required(),
                DatePicker::make('effective_from')
                    ->label('Berlaku Dari')
                    ->required(),
                DatePicker::make('effective_until')
                    ->label('Berlaku Sampai'),
            ]);
    }
}
