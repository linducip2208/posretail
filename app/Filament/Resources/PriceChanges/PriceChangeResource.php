<?php

namespace App\Filament\Resources\PriceChanges;

use App\Filament\Resources\PriceChanges\Pages\ListPriceChanges;
use App\Filament\Resources\PriceChanges\Tables\PriceChangesTable;
use App\Models\PriceChange;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PriceChangeResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '📦 Inventory';

    protected static ?int $navigationSort = 9;

    protected static ?string $model = PriceChange::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static ?string $navigationLabel = 'Riwayat Harga';

    protected static ?string $label = 'Riwayat Perubahan Harga';

    protected static ?string $recordTitleAttribute = 'id';

    public static function table(Table $table): Table
    {
        return PriceChangesTable::configure($table);
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
            'index' => ListPriceChanges::route('/'),
        ];
    }
}
