<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->inlineLabel()
            ->components([
                Select::make('table_id')
                    ->relationship('table', 'name')
                    ->label('Meja')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->label('Pelanggan')
                    ->nullable()
                    ->searchable()
                    ->preload(),

                TextInput::make('customer_name')
                    ->label('Nama Pelanggan')
                    ->required()
                    ->maxLength(255),

                TextInput::make('customer_phone')
                    ->label('Telepon')
                    ->tel()
                    ->maxLength(20),

                DatePicker::make('reservation_date')
                    ->label('Tanggal Reservasi')
                    ->required()
                    ->default(today()),

                Select::make('time_slot')
                    ->label('Slot Waktu')
                    ->required()
                    ->options([
                        '08:00' => '08:00',
                        '10:00' => '10:00',
                        '12:00' => '12:00',
                        '14:00' => '14:00',
                        '16:00' => '16:00',
                        '18:00' => '18:00',
                        '20:00' => '20:00',
                    ]),

                TextInput::make('guest_count')
                    ->label('Jumlah Tamu')
                    ->numeric()
                    ->required()
                    ->default(1),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
