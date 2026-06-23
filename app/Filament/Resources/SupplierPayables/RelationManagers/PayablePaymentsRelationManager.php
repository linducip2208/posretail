<?php

namespace App\Filament\Resources\SupplierPayables\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayablePaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payablePayments';

    protected static ?string $title = 'Pembayaran';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->numeric()
                    ->required(),
                TextInput::make('payment_method'),
                TextInput::make('reference_number'),
                DatePicker::make('payment_date'),
                Textarea::make('notes'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amount')
                    ->money('IDR'),
                TextColumn::make('payment_method'),
                TextColumn::make('reference_number')
                    ->searchable(),
                TextColumn::make('payment_date')
                    ->date(),
                TextColumn::make('notes')
                    ->limit(30),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
