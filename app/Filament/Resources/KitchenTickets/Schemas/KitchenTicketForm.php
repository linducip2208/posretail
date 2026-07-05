<?php

namespace App\Filament\Resources\KitchenTickets\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KitchenTicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->label('Pesanan')
                    ->relationship('order', 'order_number')
                    ->searchable()
                    ->disabled()
                    ->dehydrated(),
                Select::make('outlet_id')
                    ->label('Outlet')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id'))
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('ticket_number')
                    ->label('No. Tiket')
                    ->disabled()
                    ->dehydrated(),
                Repeater::make('items')
                    ->label('Item Pesanan')
                    ->schema([
                        TextInput::make('product_name')
                            ->label('Produk')
                            ->disabled(),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('notes')
                            ->label('Catatan')
                            ->disabled(),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'cooking' => 'Dimasak',
                        'ready' => 'Siap',
                        'served' => 'Sudah Disajikan',
                    ])
                    ->required()
                    ->default('pending'),
                Select::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'normal' => 'Normal',
                        'urgent' => 'Penting',
                    ])
                    ->required()
                    ->default('normal'),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
