<?php

namespace App\Filament\Imports;

use App\Models\BankStatement;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class BankStatementImporter extends Importer
{
    protected static ?string $model = BankStatement::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('transaction_date')
                ->requiredMapping()
                ->rules(['required', 'date'])
                ->label('Tanggal'),

            ImportColumn::make('bank_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->label('Bank'),

            ImportColumn::make('description')
                ->requiredMapping()
                ->rules(['required', 'string'])
                ->label('Deskripsi'),

            ImportColumn::make('reference')
                ->rules(['nullable', 'string', 'max:255'])
                ->label('Referensi'),

            ImportColumn::make('debit')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->label('Debit'),

            ImportColumn::make('credit')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->label('Kredit'),

            ImportColumn::make('balance')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->label('Saldo'),
        ];
    }

    public function resolveRecord(): ?BankStatement
    {
        return new BankStatement;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import statement bank selesai. ' . number_format($import->successful_rows) . ' statement berhasil diimport.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diimport.';
        }

        return $body;
    }
}
