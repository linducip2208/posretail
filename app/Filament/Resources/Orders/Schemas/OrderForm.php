<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\TableResto;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required(),
                Select::make('order_type')
                    ->options([
                        'dine_in' => 'Dine In',
                        'takeaway' => 'Takeaway',
                        'delivery' => 'Delivery',
                    ])
                    ->default('dine_in')
                    ->required(),
                TextInput::make('queue_number')
                    ->label('Nomor Antrian')
                    ->disabled()
                    ->helperText('Auto-generated saat checkout'),
                Select::make('table_id')
                    ->label('Meja')
                    ->options(fn ($get) => TableResto::where('status', 'available')
                        ->when($get('outlet_id'), fn ($q, $oid) => $q->where('outlet_id', $oid))
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn ($get) => $get('order_type') === 'dine_in'),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable(),
                Select::make('outlet_id')
                    ->options(fn () => auth()->user()?->accessibleOutlets()->pluck('name', 'id'))
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Kasir')
                    ->required(),
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->label('Pegawai (Waiter)')
                    ->searchable(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('deposit_amount')
                    ->label('Uang Muka (DP)')
                    ->numeric()
                    ->default(0),
                TextInput::make('remaining_amount')
                    ->label('Sisa Pembayaran')
                    ->numeric()
                    ->disabled(),
                Toggle::make('is_installment')
                    ->label('Cicilan / Kasbon')
                    ->live(),
                Select::make('installment_period')
                    ->label('Periode Cicilan')
                    ->options([
                        'weekly' => 'Mingguan',
                        'biweekly' => '2 Mingguan',
                        'monthly' => 'Bulanan',
                    ])
                    ->visible(fn ($get) => $get('is_installment')),
                TextInput::make('installment_count')
                    ->label('Jumlah Cicilan')
                    ->numeric()
                    ->default(1)
                    ->visible(fn ($get) => $get('is_installment')),
                TextInput::make('payment_status')
                    ->required()
                    ->default('unpaid'),
                TextInput::make('order_status')
                    ->required()
                    ->default('pending'),
                Textarea::make('order_notes')
                    ->label('Catatan Tambahan')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
