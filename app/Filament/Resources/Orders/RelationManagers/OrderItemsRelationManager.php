<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $title = 'Item Pesanan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required(),
                Select::make('product_variant_id')
                    ->relationship('productVariant', 'name')
                    ->searchable(),
                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->default(1),
                TextInput::make('unit_price')
                    ->numeric()
                    ->required()
                    ->default(0),
                TextInput::make('discount_percent')
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_amount')
                    ->numeric()
                    ->default(0),
                TextInput::make('subtotal')
                    ->numeric()
                    ->required()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('productVariant.name'),
                TextColumn::make('quantity')
                    ->numeric(),
                TextColumn::make('unit_price')
                    ->numeric()
                    ->money('IDR'),
                TextColumn::make('discount_percent')
                    ->label('Diskon %')
                    ->numeric(),
                TextColumn::make('discount_amount')
                    ->numeric()
                    ->money('IDR'),
                TextColumn::make('subtotal')
                    ->numeric()
                    ->money('IDR'),
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
