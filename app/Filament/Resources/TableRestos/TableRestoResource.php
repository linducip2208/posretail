<?php

namespace App\Filament\Resources\TableRestos;

use App\Filament\Resources\TableRestos\Pages\CreateTableResto;
use App\Filament\Resources\TableRestos\Pages\EditTableResto;
use App\Filament\Resources\TableRestos\Pages\ListTableRestos;
use App\Filament\Resources\TableRestos\Schemas\TableRestoForm;
use App\Filament\Resources\TableRestos\Tables\TableRestosTable;
use App\Models\TableResto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TableRestoResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string|\UnitEnum|null $navigationGroup = '🔄 Operasional';

    protected static ?int $navigationSort = 2;

    protected static ?string $model = TableResto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TableRestoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TableRestosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTableRestos::route('/'),
            'create' => CreateTableResto::route('/create'),
            'edit' => EditTableResto::route('/{record}/edit'),
        ];
    }
}
