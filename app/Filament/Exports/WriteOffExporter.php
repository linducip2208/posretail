<?php

namespace App\Filament\Exports;

use App\Models\WriteOff;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class WriteOffExporter extends Exporter
{
    protected static ?string $model = WriteOff::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('writeoff_number')->label('No. Write-Off'),
            ExportColumn::make('product.name')->label('Produk'),
            ExportColumn::make('quantity')->label('Jumlah'),
            ExportColumn::make('unit_cost')->label('Harga Satuan'),
            ExportColumn::make('total_loss')->label('Total Kerugian'),
            ExportColumn::make('reason')->label('Alasan'),
            ExportColumn::make('outlet.name')->label('Outlet'),
            ExportColumn::make('user.name')->label('Dibuat Oleh'),
            ExportColumn::make('notes')->label('Catatan'),
            ExportColumn::make('created_at')->label('Tanggal'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor write-off selesai. ' . number_format($export->successful_rows) . ' baris berhasil.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
