<?php

namespace App\Filament\Resources\StockOpnames\RelationManagers;

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

class StockOpnameItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockOpnameItems';

    protected static ?string $title = 'Item Stock Opname';

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
                TextInput::make('system_stock')
                    ->numeric()
                    ->required()
                    ->default(0),
                TextInput::make('actual_stock')
                    ->numeric()
                    ->required()
                    ->default(0),
                TextInput::make('difference')
                    ->numeric()
                    ->disabled()
                    ->hint('Dihitung otomatis: actual_stock - system_stock'),
                Textarea::make('notes'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('productVariant.name'),
                TextColumn::make('system_stock')
                    ->numeric(),
                TextColumn::make('actual_stock')
                    ->numeric(),
                TextColumn::make('difference')
                    ->numeric()
                    ->color(fn ($state): string => $state < 0 ? 'danger' : 'success'),
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
