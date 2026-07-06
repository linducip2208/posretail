<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Varian Produk';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('sku')
                    ->required()
                    ->unique('product_variants', 'sku', ignoreRecord: true),
                TextInput::make('barcode')
                    ->unique('product_variants', 'barcode', ignoreRecord: true)
                    ->suffixAction(
                        Action::make('generateVariantBarcode')
                            ->icon('heroicon-m-arrow-path')
                            ->tooltip('Generate barcode otomatis')
                            ->action(function ($set) {
                                $set('barcode', \App\Helpers\BarcodeHelper::generate());
                            })
                    ),
                TextInput::make('cost_price')
                    ->numeric()
                    ->default(0),
                TextInput::make('selling_price')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('current_stock')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('sku'),
                TextColumn::make('barcode'),
                ImageColumn::make('barcode_image')
                    ->getStateUsing(fn ($record) => $record->barcode ? route('barcode.image', ['code' => $record->barcode, 'width' => 1, 'height' => 25]) : null)
                    ->label('Barcode')
                    ->size(120)
                    ->defaultImageUrl(null),
                TextColumn::make('cost_price')
                    ->money('IDR'),
                TextColumn::make('selling_price')
                    ->money('IDR'),
                TextColumn::make('current_stock')
                    ->numeric(),
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
