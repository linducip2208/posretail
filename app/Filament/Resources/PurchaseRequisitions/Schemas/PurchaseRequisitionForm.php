<?php

namespace App\Filament\Resources\PurchaseRequisitions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseRequisitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('outlet_id')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id'))
                    ->required()
                    ->label('Outlet'),
                DatePicker::make('date_needed')
                    ->required()
                    ->default(now()->addDays(3))
                    ->minDate(now())
                    ->label('Tanggal Dibutuhkan'),
                Textarea::make('notes')
                    ->columnSpanFull()
                    ->label('Catatan'),
                Repeater::make('items')
                    ->relationship()
                    ->addActionLabel('Tambah Item')
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $product = \App\Models\Product::find($state);
                                    $set('current_stock_snapshot', $product?->current_stock ?? 0);
                                }
                            })
                            ->label('Produk'),
                        TextInput::make('current_stock_snapshot')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(true)
                            ->label('Stok Saat Ini'),
                        TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->label('Jumlah'),
                        TextInput::make('reason')
                            ->maxLength(255)
                            ->label('Alasan'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
