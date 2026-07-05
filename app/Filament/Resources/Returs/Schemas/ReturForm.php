<?php

namespace App\Filament\Resources\Returs\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReturForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->label('Pesanan')
                    ->relationship('order', 'order_number')
                    ->searchable()
                    ->required()
                    ->live(),
                Select::make('outlet_id')
                    ->label('Outlet')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id'))
                    ->required(),
                Select::make('user_id')
                    ->label('Kasir')
                    ->relationship('user', 'name')
                    ->required(),
                Textarea::make('reason')
                    ->label('Alasan Retur')
                    ->required()
                    ->columnSpanFull(),
                Repeater::make('returnItems')
                    ->label('Item Retur')
                    ->relationship('returnItems')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $product = \App\Models\Product::find($state);
                                if ($product) {
                                    $set('unit_price', $product->selling_price ?? 0);
                                    $qty = (int) ($get('quantity') ?? 1);
                                    $price = (float) ($product->selling_price ?? 0);
                                    $set('subtotal', $qty * $price);
                                }
                            }),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $qty = (int) ($state ?? 1);
                                $price = (float) ($get('unit_price') ?? 0);
                                $set('subtotal', $qty * $price);
                            }),
                        TextInput::make('unit_price')
                            ->label('Harga Refund')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $qty = (int) ($get('quantity') ?? 0);
                                $price = (float) ($state ?? 0);
                                $set('subtotal', $qty * $price);
                            }),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->disabled()
                            ->prefix('Rp'),
                    ])
                    ->addActionLabel('Tambah Item')
                    ->columns(4)
                    ->columnSpanFull(),
                TextInput::make('total_amount')
                    ->label('Total Refund')
                    ->numeric()
                    ->disabled()
                    ->prefix('Rp')
                    ->default(0),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                    ])
                    ->required()
                    ->default('pending'),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
