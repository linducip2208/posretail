<?php

namespace App\Filament\Resources\PurchaseRequisitions;

use App\Filament\Resources\PurchaseRequisitions\Pages\CreatePurchaseRequisition;
use App\Filament\Resources\PurchaseRequisitions\Pages\EditPurchaseRequisition;
use App\Filament\Resources\PurchaseRequisitions\Pages\ListPurchaseRequisitions;
use App\Filament\Resources\PurchaseRequisitions\Schemas\PurchaseRequisitionForm;
use App\Filament\Resources\PurchaseRequisitions\Tables\PurchaseRequisitionsTable;
use App\Models\PurchaseRequisition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PurchaseRequisitionResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '🛒 Pembelian';

    protected static ?int $navigationSort = 4;

    protected static ?string $model = PurchaseRequisition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'pr_number';

    protected static ?string $label = 'Permintaan Pembelian';

    protected static ?string $pluralLabel = 'Permintaan Pembelian';

    public static function form(Schema $schema): Schema
    {
        return PurchaseRequisitionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseRequisitionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PurchaseRequisitionItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseRequisitions::route('/'),
            'create' => CreatePurchaseRequisition::route('/create'),
            'edit' => EditPurchaseRequisition::route('/{record}/edit'),
        ];
    }
}
