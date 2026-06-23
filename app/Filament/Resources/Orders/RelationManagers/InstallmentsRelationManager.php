<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstallmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'installments';

    protected static ?string $title = 'Cicilan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('installment_number')
                    ->numeric()
                    ->required()
                    ->default(1),
                TextInput::make('amount')
                    ->numeric()
                    ->required()
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
                    ->default('pending'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('installment_number'),
                TextColumn::make('amount')
                    ->numeric()
                    ->money('IDR'),
                TextColumn::make('due_date')
                    ->date(),
                TextColumn::make('paid_date')
                    ->date(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
