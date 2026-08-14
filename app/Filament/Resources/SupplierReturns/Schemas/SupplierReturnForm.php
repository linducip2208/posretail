<?php

namespace App\Filament\Resources\SupplierReturns\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SupplierReturnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Supplier'),
                Select::make('purchase_order_id')
                    ->relationship('purchaseOrder', 'po_number')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('Referensi Purchase Order'),
                Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Outlet'),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Diajukan',
                        'received' => 'Diterima',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->required()
                    ->default('draft')
                    ->label('Status'),
                Textarea::make('reason')
                    ->rows(3)
                    ->label('Alasan Retur'),
                Textarea::make('notes')
                    ->rows(3)
                    ->label('Catatan'),
            ]);
    }
}
