<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug((string) $state))),
                TextInput::make('slug')
                    ->helperText('Dikosongkan akan dibuat otomatis dari nama produk.'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('category_id')
                    ->relationship('category', 'name'),
                Select::make('brand_id')
                    ->relationship('brand', 'name'),
                Select::make('unit_id')
                    ->relationship('unit', 'name'),
                Select::make('outlet_id')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id')),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('barcode')
                    ->unique('products', 'barcode', ignoreRecord: true)
                    ->suffixAction(
                        Action::make('generateBarcode')
                            ->icon('heroicon-m-arrow-path')
                            ->tooltip('Generate barcode otomatis')
                            ->action(function ($set) {
                                $set('barcode', \App\Helpers\BarcodeHelper::generate());
                            })
                    ),
                TextInput::make('cost_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('selling_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('wholesale_price')
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('member_price')
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('min_stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('max_stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('current_stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                FileUpload::make('image')
                    ->image(),
                Toggle::make('has_variants')
                    ->required(),
                Toggle::make('active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
