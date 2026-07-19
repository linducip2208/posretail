<?php

namespace App\Filament\Resources\LoyaltyRewards;

use App\Filament\Resources\LoyaltyRewards\Pages\CreateLoyaltyReward;
use App\Filament\Resources\LoyaltyRewards\Pages\EditLoyaltyReward;
use App\Filament\Resources\LoyaltyRewards\Pages\ListLoyaltyRewards;
use App\Filament\Resources\LoyaltyRewards\Schemas\LoyaltyRewardForm;
use App\Filament\Resources\LoyaltyRewards\Tables\LoyaltyRewardsTable;
use App\Models\LoyaltyReward;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LoyaltyRewardResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '👥 Customer';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Hadiah / Reward';

    protected static ?string $model = LoyaltyReward::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LoyaltyRewardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoyaltyRewardsTable::configure($table);
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
            'index' => ListLoyaltyRewards::route('/'),
            'create' => CreateLoyaltyReward::route('/create'),
            'edit' => EditLoyaltyReward::route('/{record}/edit'),
        ];
    }
}
