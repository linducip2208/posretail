<?php

namespace App\Filament\Resources\MarketplaceOrders\Pages;

use App\Filament\Exports\MarketplaceOrderExporter;
use App\Filament\Resources\MarketplaceOrders\MarketplaceOrderResource;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Resources\Pages\ListRecords;

class ListMarketplaceOrders extends ListRecords
{
    protected static string $resource = MarketplaceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(MarketplaceOrderExporter::class)
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make()
                ->exporter(MarketplaceOrderExporter::class)
                ->label('Export Terpilih'),
        ];
    }
}
