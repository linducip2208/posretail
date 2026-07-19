<?php

namespace App\Filament\Resources\SupplierPayables;

use App\Filament\Resources\SupplierPayables\Pages\CreateSupplierPayable;
use App\Filament\Resources\SupplierPayables\Pages\EditSupplierPayable;
use App\Filament\Resources\SupplierPayables\Pages\ListSupplierPayables;
use App\Filament\Resources\SupplierPayables\Schemas\SupplierPayableForm;
use App\Filament\Resources\SupplierPayables\Tables\SupplierPayablesTable;
use App\Models\SupplierPayable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierPayableResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '🛒 Pembelian';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Supplier Invoice';

    protected static ?string $model = SupplierPayable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function form(Schema $schema): Schema
    {
        return SupplierPayableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierPayablesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PayablePaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupplierPayables::route('/'),
            'create' => CreateSupplierPayable::route('/create'),
            'edit' => EditSupplierPayable::route('/{record}/edit'),
        ];
    }
}
