<?php

namespace App\Filament\Exports;

use App\Models\PurchaseOrder;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PurchaseOrderExporter extends Exporter
{
    protected static ?string $model = PurchaseOrder::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('po_number')
                ->label('No. PO'),
            ExportColumn::make('created_at')
                ->label('Tanggal'),
            ExportColumn::make('supplier.name')
                ->label('Supplier'),
            ExportColumn::make('outlet.name')
                ->label('Outlet'),
            ExportColumn::make('user.name')
                ->label('Dibuat Oleh'),
            ExportColumn::make('total_amount')
                ->label('Total'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('notes')
                ->label('Catatan'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor purchase order selesai. ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
