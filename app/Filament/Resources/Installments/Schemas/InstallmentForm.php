<?php

namespace App\Filament\Resources\Installments\Schemas;

use Filament\Forms\Components\DatePicker;
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
                    ->relationship('order', 'order_number')
                    ->required()
                    ->searchable(),
                TextInput::make('installment_number')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                DatePicker::make('due_date')
                    ->required(),
                DatePicker::make('paid_date'),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }
}
