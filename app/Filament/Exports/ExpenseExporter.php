<?php

namespace App\Filament\Exports;

use App\Models\Expense;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ExpenseExporter extends Exporter
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('outlet.name')->label('Outlet'),
            ExportColumn::make('expense_date')->label('Tanggal'),
            ExportColumn::make('category')->label('Kategori'),
            ExportColumn::make('amount')->label('Jumlah'),
            ExportColumn::make('description')->label('Deskripsi'),
            ExportColumn::make('reference_number')->label('Nomor Referensi'),
            ExportColumn::make('notes')->label('Catatan'),
            ExportColumn::make('user.name')->label('Dibuat Oleh'),
            ExportColumn::make('created_at')->label('Dibuat'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor pengeluaran selesai. ' . number_format($export->successful_rows) . ' baris berhasil.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
