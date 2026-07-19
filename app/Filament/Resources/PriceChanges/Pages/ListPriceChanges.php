<?php

namespace App\Filament\Resources\PriceChanges\Pages;

use App\Filament\Exports\PriceChangeExporter;
use App\Filament\Resources\PriceChanges\PriceChangeResource;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Resources\Pages\ListRecords;

class ListPriceChanges extends ListRecords
{
    protected static string $resource = PriceChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(PriceChangeExporter::class)
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make()
                ->exporter(PriceChangeExporter::class)
                ->label('Export Terpilih'),
        ];
    }
}
