<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),
                Select::make('outlet_id')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id'))
                    ->required(),
                DatePicker::make('date')
                    ->required(),
                TextInput::make('clock_in')
                    ->type('time'),
                TextInput::make('clock_out')
                    ->type('time'),
                Select::make('status')
                    ->options([
                        'present' => 'Present',
                        'late' => 'Late',
                        'absent' => 'Absent',
                        'leave' => 'Leave',
                        'sick' => 'Sick',
                    ])
                    ->required()
                    ->default('present'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
