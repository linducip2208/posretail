<?php

namespace App\Filament\Resources\VolumePricings;

use App\Filament\Resources\VolumePricings\Pages\CreateVolumePricing;
use App\Filament\Resources\VolumePricings\Pages\EditVolumePricing;
use App\Filament\Resources\VolumePricings\Pages\ListVolumePricings;
use App\Filament\Resources\VolumePricings\Schemas\VolumePricingForm;
use App\Filament\Resources\VolumePricings\Tables\VolumePricingsTable;
use App\Models\VolumePricing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VolumePricingResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '📦 Inventory';

    protected static ?int $navigationSort = 12;

    protected static ?string $navigationLabel = 'Harga Bertingkat';

    protected static ?string $model = VolumePricing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return VolumePricingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VolumePricingsTable::configure($table);
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
            'index' => ListVolumePricings::route('/'),
            'create' => CreateVolumePricing::route('/create'),
            'edit' => EditVolumePricing::route('/{record}/edit'),
        ];
    }
}
