<?php

namespace App\Filament\Resources\DiscountTemplates;

use App\Filament\Resources\DiscountTemplates\Pages\CreateDiscountTemplate;
use App\Filament\Resources\DiscountTemplates\Pages\EditDiscountTemplate;
use App\Filament\Resources\DiscountTemplates\Pages\ListDiscountTemplates;
use App\Filament\Resources\DiscountTemplates\Schemas\DiscountTemplateForm;
use App\Filament\Resources\DiscountTemplates\Tables\DiscountTemplatesTable;
use App\Models\DiscountTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DiscountTemplateResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '🎁 Promo';

    protected static ?int $navigationSort = 1;

    protected static ?string $model = DiscountTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DiscountTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiscountTemplatesTable::configure($table);
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
            'index' => ListDiscountTemplates::route('/'),
            'create' => CreateDiscountTemplate::route('/create'),
            'edit' => EditDiscountTemplate::route('/{record}/edit'),
        ];
    }
}
