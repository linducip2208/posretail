<?php

namespace App\Filament\Resources\Installments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InstallmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->label('Pesanan')
                    ->relationship('order', 'order_number')
                    ->required()
                    ->searchable(),
                TextInput::make('installment_number')
                    ->label('Angsuran Ke')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('amount')
                    ->label('Jumlah')
                    ->required()
                    ->numeric()
                    ->default(0),
                DatePicker::make('due_date')
                    ->label('Jatuh Tempo')
                    ->required(),
                DatePicker::make('paid_date')
                    ->label('Tanggal Bayar'),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                    ])
                    ->required()
                    ->default('pending'),
                Repeater::make('schedules')
                    ->label('Jadwal Angsuran')
                    ->relationship('schedules')
                    ->schema([
                        TextInput::make('sequence')
                            ->label('Urutan')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(true),
                        DatePicker::make('due_date')
                            ->label('Jatuh Tempo')
                            ->required(),
                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->default(0),
                    ])
                    ->addActionLabel('Tambah Jadwal')
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
