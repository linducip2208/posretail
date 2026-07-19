<?php

namespace App\Filament\Resources\CustomerDeposits\Pages;

use App\Filament\Exports\CustomerDepositExporter;
use App\Filament\Resources\CustomerDeposits\CustomerDepositResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerDeposits extends ListRecords
{
    protected static string $resource = CustomerDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(CustomerDepositExporter::class)
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make()
                ->exporter(CustomerDepositExporter::class)
                ->label('Export Terpilih'),
        ];
    }
}
