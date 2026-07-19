<?php

namespace App\Filament\Resources\LoyaltyPoints;

use App\Filament\Resources\LoyaltyPoints\Pages\CreateLoyaltyPoint;
use App\Filament\Resources\LoyaltyPoints\Pages\EditLoyaltyPoint;
use App\Filament\Resources\LoyaltyPoints\Pages\ListLoyaltyPoints;
use App\Filament\Resources\LoyaltyPoints\Schemas\LoyaltyPointForm;
use App\Filament\Resources\LoyaltyPoints\Tables\LoyaltyPointsTable;
use App\Models\LoyaltyPoint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LoyaltyPointResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '👥 Customer';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Poin Pelanggan';

    protected static ?string $pluralModelLabel = 'Poin Pelanggan';

    protected static ?string $model = LoyaltyPoint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return LoyaltyPointForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoyaltyPointsTable::configure($table);
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
            'index' => ListLoyaltyPoints::route('/'),
            'create' => CreateLoyaltyPoint::route('/create'),
            'edit' => EditLoyaltyPoint::route('/{record}/edit'),
        ];
    }
}
