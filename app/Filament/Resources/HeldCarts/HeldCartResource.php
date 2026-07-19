<?php

namespace App\Filament\Resources\HeldCarts;

use App\Filament\Resources\HeldCarts\Pages\CreateHeldCart;
use App\Filament\Resources\HeldCarts\Pages\EditHeldCart;
use App\Filament\Resources\HeldCarts\Pages\ListHeldCarts;
use App\Filament\Resources\HeldCarts\Schemas\HeldCartForm;
use App\Filament\Resources\HeldCarts\Tables\HeldCartsTable;
use App\Models\HeldCart;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HeldCartResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '💰 Penjualan';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Hold / Suspend';

    protected static ?string $model = HeldCart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPauseCircle;

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return HeldCartForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HeldCartsTable::configure($table);
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
            'index' => ListHeldCarts::route('/'),
            'create' => CreateHeldCart::route('/create'),
            'edit' => EditHeldCart::route('/{record}/edit'),
        ];
    }
}
