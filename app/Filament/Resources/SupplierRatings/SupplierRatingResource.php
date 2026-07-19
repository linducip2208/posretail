<?php

namespace App\Filament\Resources\SupplierRatings;

use App\Filament\Resources\SupplierRatings\Pages\CreateSupplierRating;
use App\Filament\Resources\SupplierRatings\Pages\EditSupplierRating;
use App\Filament\Resources\SupplierRatings\Pages\ListSupplierRatings;
use App\Filament\Resources\SupplierRatings\Schemas\SupplierRatingForm;
use App\Filament\Resources\SupplierRatings\Tables\SupplierRatingsTable;
use App\Models\SupplierRating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierRatingResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '🚚 Supplier';

    protected static ?int $navigationSort = 2;

    protected static ?string $model = SupplierRating::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $recordTitleAttribute = 'supplier.name';

    protected static ?string $label = 'Rating Supplier';

    protected static ?string $pluralLabel = 'Rating Supplier';

    public static function form(Schema $schema): Schema
    {
        return SupplierRatingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierRatingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupplierRatings::route('/'),
            'create' => CreateSupplierRating::route('/create'),
            'edit' => EditSupplierRating::route('/{record}/edit'),
        ];
    }
}
