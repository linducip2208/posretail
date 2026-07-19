<?php

namespace App\Filament\Resources\WriteOffs;

use App\Filament\Resources\WriteOffs\Pages\CreateWriteOff;
use App\Filament\Resources\WriteOffs\Pages\ListWriteOffs;
use App\Filament\Resources\WriteOffs\Schemas\WriteOffForm;
use App\Filament\Resources\WriteOffs\Tables\WriteOffsTable;
use App\Models\WriteOff;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WriteOffResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '📦 Inventory';

    protected static ?int $navigationSort = 13;

    protected static ?string $model = WriteOff::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrash;

    protected static ?string $navigationLabel = 'Write-Off Barang';

    protected static ?string $recordTitleAttribute = 'writeoff_number';

    public static function form(Schema $schema): Schema
    {
        return WriteOffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WriteOffsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWriteOffs::route('/'),
            'create' => CreateWriteOff::route('/create'),
        ];
    }
}
