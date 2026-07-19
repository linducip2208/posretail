<?php

namespace App\Filament\Resources\BinLocations;

use App\Filament\Resources\BinLocations\Pages\CreateBinLocation;
use App\Filament\Resources\BinLocations\Pages\EditBinLocation;
use App\Filament\Resources\BinLocations\Pages\ListBinLocations;
use App\Filament\Resources\BinLocations\Schemas\BinLocationForm;
use App\Filament\Resources\BinLocations\Tables\BinLocationsTable;
use App\Models\BinLocation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BinLocationResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '📦 Inventory';

    protected static ?int $navigationSort = 16;

    protected static ?string $model = BinLocation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $label = 'Lokasi Rak';

    protected static ?string $pluralLabel = 'Lokasi Rak';

    public static function form(Schema $schema): Schema
    {
        return BinLocationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BinLocationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBinLocations::route('/'),
            'create' => CreateBinLocation::route('/create'),
            'edit' => EditBinLocation::route('/{record}/edit'),
        ];
    }
}
