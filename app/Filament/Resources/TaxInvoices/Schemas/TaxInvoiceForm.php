<?php

namespace App\Filament\Resources\TaxInvoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaxInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $order = \App\Models\Order::find($state);
                            if ($order) {
                                $dpp = round($order->total_amount * 100 / 111, 2);
                                $ppn = round($dpp * 0.11, 2);
                                $set('dpp', $dpp);
                                $set('ppn_amount', $ppn);
                                $set('total_amount', $order->total_amount);
                                if ($order->customer) {
                                    $set('customer_name', $order->customer->name);
                                    $set('customer_address', $order->customer->address);
                                }
                            }
                        }
                    })
                    ->label('Pesanan'),
                TextInput::make('customer_name')
                    ->required()
                    ->label('Nama Pelanggan'),
                TextInput::make('customer_npwp')
                    ->label('NPWP')
                    ->maxLength(20),
                Textarea::make('customer_address')
                    ->columnSpanFull()
                    ->label('Alamat Pelanggan'),
                DatePicker::make('invoice_date')
                    ->required()
                    ->default(now())
                    ->label('Tanggal Faktur'),
                TextInput::make('reference_number')
                    ->label('Nomor Referensi'),
                Textarea::make('notes')
                    ->columnSpanFull()
                    ->label('Catatan'),
            ]);
    }
}
