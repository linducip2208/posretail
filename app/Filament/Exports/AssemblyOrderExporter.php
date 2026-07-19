<?php

namespace App\Filament\Exports;

use App\Models\AssemblyOrder;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AssemblyOrderExporter extends Exporter
{
    protected static ?string $model = AssemblyOrder::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('assembly_number')
                ->label('No. Produksi'),
            ExportColumn::make('product.name')
                ->label('Produk'),
            ExportColumn::make('quantity')
                ->label('Jumlah'),
            ExportColumn::make('outlet.name')
                ->label('Outlet'),
            ExportColumn::make('user.name')
                ->label('Dibuat Oleh'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('notes')
                ->label('Catatan'),
            ExportColumn::make('completed_at')
                ->label('Selesai Pada'),
            ExportColumn::make('created_at')
                ->label('Dibuat Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor produksi selesai. ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
