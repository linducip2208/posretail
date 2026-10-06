<?php

namespace App\Filament\Resources\SupplierRatings;

use App\Filament\Resources\Concerns\AuthorizesByNavigation;

use App\Filament\Resources\SupplierRatings\Pages\CreateSupplierRating;
use App\Filament\Resources\SupplierRatings\Pages\EditSupplierRating;
use App\Filament\Resources\SupplierRatings\Pages\ListSupplierRatings;
use App\Filament\Resources\SupplierRatings\Schemas\SupplierRatingForm;
use App\Filament\Resources\SupplierRatings\Tables\SupplierRatingsTable;
use App\Models\SupplierRating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupplierRatingResource extends Resource
{
    use AuthorizesByNavigation;
    protected static string|\UnitEnum|null $navigationGroup = 'Pembelian';

    protected static ?int $navigationSort = 2;

    protected static ?string $model = SupplierRating::class;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-heart';

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

    /**
     * Rating mengikuti outlet purchase order-nya. Non-admin hanya melihat
     * rating dari PO di outlet yang boleh diaksesnya (fail-closed).
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasPermission('*')) {
            return $query;
        }

        $ids = $user->getAccessibleOutletIds();

        return $query->whereHas('purchaseOrder', fn ($q) => $q->whereIn('outlet_id', $ids));
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
