<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Exports\ExpenseExporter;
use App\Filament\Resources\Expenses\ExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Resources\Pages\ListRecords;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(ExpenseExporter::class)
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make()
                ->exporter(ExpenseExporter::class)
                ->label('Export Terpilih'),
        ];
    }
}
