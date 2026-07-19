<?php

namespace App\Filament\Resources\Deliveries\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeliveryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->inlineLabel()
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->label('Pesanan')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('driver_id')
                    ->relationship('driver', 'name')
                    ->label('Driver')
                    ->searchable()
                    ->preload(),

                Textarea::make('shipping_address')
                    ->label('Alamat Pengiriman')
                    ->columnSpanFull(),

                TextInput::make('recipient_name')
                    ->label('Nama Penerima')
                    ->required(),

                TextInput::make('recipient_phone')
                    ->label('Telepon Penerima')
                    ->tel(),

                TextInput::make('tracking_number')
                    ->label('No. Resi'),

                Textarea::make('delivery_notes')
                    ->label('Catatan Pengiriman')
                    ->columnSpanFull(),
            ]);
    }
}
