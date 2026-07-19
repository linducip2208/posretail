<?php

namespace App\Filament\Resources\BankStatements\Pages;

use App\Filament\Exports\BankStatementExporter;
use App\Filament\Imports\BankStatementImporter;
use App\Filament\Resources\BankStatements\BankStatementResource;
use App\Models\BankStatement;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListBankStatements extends ListRecords
{
    protected static string $resource = BankStatementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(BankStatementImporter::class)
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray'),
            ExportAction::make()
                ->exporter(BankStatementExporter::class)
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\BankStatements\Widgets\BankStatementSummary::class,
        ];
    }
}
