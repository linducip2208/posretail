<?php

namespace App\Filament\Resources\AssemblyOrders\Pages;

use App\Filament\Exports\AssemblyOrderExporter;
use App\Filament\Resources\AssemblyOrders\AssemblyOrderResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListAssemblyOrders extends ListRecords
{
    protected static string $resource = AssemblyOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(AssemblyOrderExporter::class)
                ->label('Export Produksi')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }
}
