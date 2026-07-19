<?php

namespace App\Filament\Exports;

use App\Models\Bundle;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BundleExporter extends Exporter
{
    protected static ?string $model = Bundle::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('Nama'),
            ExportColumn::make('slug')
                ->label('Slug'),
            ExportColumn::make('bundle_price')
                ->label('Harga Bundle'),
            ExportColumn::make('items.count')
                ->label('Jumlah Item'),
            ExportColumn::make('active')
                ->label('Aktif'),
            ExportColumn::make('start_date')
                ->label('Tanggal Mulai'),
            ExportColumn::make('end_date')
                ->label('Tanggal Berakhir'),
            ExportColumn::make('description')
                ->label('Deskripsi'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor bundle selesai. ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
