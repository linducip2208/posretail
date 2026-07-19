<?php

namespace App\Filament\Resources\SupplierContracts;

use App\Filament\Resources\SupplierContracts\Pages\CreateSupplierContract;
use App\Filament\Resources\SupplierContracts\Pages\EditSupplierContract;
use App\Filament\Resources\SupplierContracts\Pages\ListSupplierContracts;
use App\Filament\Resources\SupplierContracts\Schemas\SupplierContractForm;
use App\Filament\Resources\SupplierContracts\Tables\SupplierContractsTable;
use App\Models\SupplierContract;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierContractResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '🚚 Supplier';

    protected static ?int $navigationSort = 3;

    protected static ?string $model = SupplierContract::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $recordTitleAttribute = 'contract_number';

    protected static ?string $label = 'Kontrak Supplier';

    protected static ?string $pluralLabel = 'Kontrak Supplier';

    public static function form(Schema $schema): Schema
    {
        return SupplierContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierContractsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupplierContracts::route('/'),
            'create' => CreateSupplierContract::route('/create'),
            'edit' => EditSupplierContract::route('/{record}/edit'),
        ];
    }
}
