<?php

namespace App\Filament\Resources\WriteOffs\Pages;

use App\Filament\Exports\WriteOffExporter;
use App\Filament\Resources\WriteOffs\WriteOffResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Resources\Pages\ListRecords;

class ListWriteOffs extends ListRecords
{
    protected static string $resource = WriteOffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(WriteOffExporter::class)
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make()
                ->exporter(WriteOffExporter::class)
                ->label('Export Terpilih'),
        ];
    }
}
