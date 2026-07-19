<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Filament\Exports\AssetExporter;
use App\Filament\Resources\Assets\CompanyAssetResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListAssets extends ListRecords
{
    protected static string $resource = CompanyAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(AssetExporter::class)
                ->label('Ekspor')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }
}
