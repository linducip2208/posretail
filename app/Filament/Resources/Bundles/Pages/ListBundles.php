<?php

namespace App\Filament\Resources\Bundles\Pages;

use App\Filament\Exports\BundleExporter;
use App\Filament\Resources\Bundles\BundleResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Resources\Pages\ListRecords;

class ListBundles extends ListRecords
{
    protected static string $resource = BundleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(BundleExporter::class)
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make()
                ->exporter(BundleExporter::class)
                ->label('Export Terpilih'),
        ];
    }
}
