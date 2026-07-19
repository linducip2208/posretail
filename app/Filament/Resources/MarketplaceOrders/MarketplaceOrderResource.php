<?php

namespace App\Filament\Resources\MarketplaceOrders;

use App\Filament\Resources\MarketplaceOrders\Pages\ListMarketplaceOrders;
use App\Filament\Resources\MarketplaceOrders\Tables\MarketplaceOrdersTable;
use App\Models\MarketplaceOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class MarketplaceOrderResource extends Resource
{
    protected static string|\UnitEnum|null $navigationGroup = '💰 Penjualan';

    protected static ?int $navigationSort = 5;

    protected static ?string $model = MarketplaceOrder::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Pesanan Marketplace';

    protected static ?string $recordTitleAttribute = 'platform_order_id';

    public static function table(Table $table): Table
    {
        return MarketplaceOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketplaceOrders::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
