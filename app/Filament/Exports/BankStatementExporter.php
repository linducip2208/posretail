<?php

namespace App\Filament\Exports;

use App\Models\BankStatement;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BankStatementExporter extends Exporter
{
    protected static ?string $model = BankStatement::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('transaction_date')
                ->label('Tanggal'),
            ExportColumn::make('bank_name')
                ->label('Bank'),
            ExportColumn::make('account_number')
                ->label('No. Rekening'),
            ExportColumn::make('description')
                ->label('Deskripsi'),
            ExportColumn::make('reference')
                ->label('Referensi'),
            ExportColumn::make('debit')
                ->label('Debit'),
            ExportColumn::make('credit')
                ->label('Kredit'),
            ExportColumn::make('balance')
                ->label('Saldo'),
            ExportColumn::make('is_matched')
                ->label('Tercocokkan')
                ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
            ExportColumn::make('outlet.name')
                ->label('Outlet'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor statement bank selesai. ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
