<?php

namespace App\Filament\Resources\Returs;

use App\Filament\Resources\Returs\Pages\CreateRetur;
use App\Filament\Resources\Returs\Pages\EditRetur;
use App\Filament\Resources\Returs\Pages\ListReturs;
use App\Filament\Resources\Returs\Schemas\ReturForm;
use App\Filament\Resources\Returs\Tables\ReturnsTable;
use App\Models\Retur;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class ReturResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '?? Transaksi';

    protected static ?int $navigationSort = 2;

    protected static ?string $model = Retur::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static ?string $navigationLabel = 'Retur / Pengembalian';

    protected static ?string $label = 'Retur / Pengembalian';

    protected static ?string $recordTitleAttribute = 'return_number';

    public static function form(Schema $schema): Schema
    {
        return ReturForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReturnsTable::configure($table);
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
            'index' => ListReturs::route('/'),
            'create' => CreateRetur::route('/create'),
            'edit' => EditRetur::route('/{record}/edit'),
        ];
    }
}
