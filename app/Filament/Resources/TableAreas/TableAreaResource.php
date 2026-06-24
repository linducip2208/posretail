<?php

namespace App\Filament\Resources\TableAreas;

use App\Filament\Resources\TableAreas\Pages\CreateTableArea;
use App\Filament\Resources\TableAreas\Pages\EditTableArea;
use App\Filament\Resources\TableAreas\Pages\ListTableAreas;
use App\Filament\Resources\TableAreas\Schemas\TableAreaForm;
use App\Filament\Resources\TableAreas\Tables\TableAreasTable;
use App\Models\TableArea;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TableAreaResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '🏪 Master Data';

    protected static ?int $navigationSort = 10;

    protected static ?string $model = TableArea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TableAreaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TableAreasTable::configure($table);
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
            'index' => ListTableAreas::route('/'),
            'create' => CreateTableArea::route('/create'),
            'edit' => EditTableArea::route('/{record}/edit'),
        ];
    }
}
