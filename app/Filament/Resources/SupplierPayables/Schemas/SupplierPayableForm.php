<?php

namespace App\Filament\Resources\SupplierPayables\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierPayableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->required()
                    ->searchable(),
                Select::make('purchase_order_id')
                    ->relationship('purchaseOrder', 'po_number')
                    ->searchable(),
                TextInput::make('invoice_number')
                    ->required(),
                TextInput::make('total_amount')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('paid_amount')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                DatePicker::make('due_date')
                    ->required(),
                Select::make('status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                    ])
                    ->required()
                    ->default('unpaid'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
