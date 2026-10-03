<?php

namespace App\Filament\Resources\SupplierReturns;

use App\Filament\Resources\Concerns\AuthorizesByNavigation;

use App\Filament\Resources\SupplierReturns\Pages\CreateSupplierReturn;
use App\Filament\Resources\SupplierReturns\Pages\EditSupplierReturn;
use App\Filament\Resources\SupplierReturns\Pages\ListSupplierReturns;
use App\Filament\Resources\SupplierReturns\Schemas\SupplierReturnForm;
use App\Filament\Resources\SupplierReturns\Tables\SupplierReturnsTable;
use App\Models\SupplierReturn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierReturnResource extends Resource
{
    use AuthorizesByNavigation;
    protected static string|\UnitEnum|null $navigationGroup = '🛒 Pembelian';

    protected static ?int $navigationSort = 6;

    protected static ?string $model = SupplierReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $navigationLabel = 'Retur Supplier';

    protected static ?string $recordTitleAttribute = 'return_number';

    public static function form(Schema $schema): Schema
    {
        return SupplierReturnForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierReturnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SupplierReturnItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupplierReturns::route('/'),
            'create' => CreateSupplierReturn::route('/create'),
            'edit' => EditSupplierReturn::route('/{record}/edit'),
        ];
    }
}
