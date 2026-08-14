<?php

namespace App\Filament\Resources\SupplierReturns\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SupplierReturnItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Item Retur';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->default(1),
                TextInput::make('unit_price')
                    ->numeric()
                    ->required()
                    ->default(0),
                Textarea::make('reason')
                    ->rows(2)
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->numeric(),
                TextColumn::make('unit_price')
                    ->numeric()
                    ->money('IDR'),
                TextColumn::make('subtotal')
                    ->numeric()
                    ->money('IDR'),
                TextColumn::make('reason')
                    ->placeholder('-')
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
