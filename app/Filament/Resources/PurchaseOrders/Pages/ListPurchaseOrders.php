<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Exports\PurchaseOrderExporter;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(PurchaseOrderExporter::class)
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make()
                ->exporter(PurchaseOrderExporter::class)
                ->label('Export Terpilih'),
        ];
    }
}
