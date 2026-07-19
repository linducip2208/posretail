<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Exports\ProductExporter;
use App\Filament\Exports\StockExporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make()
                ->importer(ProductImporter::class)
                ->label('Import Produk')
                ->icon('heroicon-o-arrow-up-tray'),
            ExportAction::make()
                ->exporter(ProductExporter::class)
                ->label('Export Produk')
                ->icon('heroicon-o-arrow-down-tray'),
            ExportAction::make()
                ->exporter(StockExporter::class)
                ->label('Export Stok')
                ->icon('heroicon-o-table-cells'),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make()
                ->exporter(ProductExporter::class)
                ->label('Export Terpilih'),
        ];
    }
}
