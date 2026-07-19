<?php

namespace App\Filament\Resources\AssemblyOrders;

use App\Filament\Resources\AssemblyOrders\Pages\CreateAssemblyOrder;
use App\Filament\Resources\AssemblyOrders\Pages\EditAssemblyOrder;
use App\Filament\Resources\AssemblyOrders\Pages\ListAssemblyOrders;
use App\Filament\Resources\AssemblyOrders\Schemas\AssemblyOrderForm;
use App\Filament\Resources\AssemblyOrders\Tables\AssemblyOrdersTable;
use App\Models\AssemblyOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssemblyOrderResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '📦 Inventory';

    protected static ?int $navigationSort = 11;

    protected static ?string $model = AssemblyOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $navigationLabel = 'Produksi';

    protected static ?string $label = 'Produksi';

    protected static ?string $pluralLabel = 'Produksi';

    protected static ?string $recordTitleAttribute = 'assembly_number';

    public static function form(Schema $schema): Schema
    {
        return AssemblyOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssemblyOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AssemblyOrderItemsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $outletIds = auth()->user()?->getAccessibleOutletIds() ?? [];
        if (empty($outletIds)) {
            return parent::getEloquentQuery();
        }
        return parent::getEloquentQuery()->whereIn('outlet_id', $outletIds);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssemblyOrders::route('/'),
            'create' => CreateAssemblyOrder::route('/create'),
            'edit' => EditAssemblyOrder::route('/{record}/edit'),
        ];
    }
}
