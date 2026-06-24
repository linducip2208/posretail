<?php

namespace App\Filament\Resources\Customers\RelationManagers;

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

class LoyaltyPointsRelationManager extends RelationManager
{
    protected static string $relationship = 'loyaltyPoints';

    protected static ?string $title = '⭐ Loyalitas';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->searchable(),
                TextInput::make('points_earned')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('points_redeemed')
                    ->numeric()
                    ->default(0),
                TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('description'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->searchable(),
                TextColumn::make('points_earned')
                    ->numeric(),
                TextColumn::make('points_redeemed')
                    ->numeric(),
                TextColumn::make('balance')
                    ->numeric(),
                TextColumn::make('description')
                    ->limit(30),
                TextColumn::make('created_at')
                    ->dateTime(),
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
