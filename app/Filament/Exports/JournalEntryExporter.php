<?php

namespace App\Filament\Exports;

use App\Models\JournalEntry;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class JournalEntryExporter extends Exporter
{
    protected static ?string $model = JournalEntry::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('journal_number')->label('Nomor Jurnal'),
            ExportColumn::make('journal_date')->label('Tanggal'),
            ExportColumn::make('description')->label('Deskripsi'),
            ExportColumn::make('reference_type')->label('Tipe Referensi'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('creator.name')->label('Dibuat Oleh'),
            ExportColumn::make('posted_at')->label('Waktu Posting'),
            ExportColumn::make('created_at')->label('Dibuat'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor jurnal selesai. ' . number_format($export->successful_rows) . ' baris berhasil.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
