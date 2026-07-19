<?php

namespace App\Filament\Exports;

use App\Models\TaxInvoice;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TaxInvoiceExporter extends Exporter
{
    protected static ?string $model = TaxInvoice::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('invoice_number')
                ->label('No. Faktur'),
            ExportColumn::make('invoice_date')
                ->label('Tanggal'),
            ExportColumn::make('order.order_number')
                ->label('No. Pesanan'),
            ExportColumn::make('customer_name')
                ->label('Pelanggan'),
            ExportColumn::make('customer_npwp')
                ->label('NPWP'),
            ExportColumn::make('customer_address')
                ->label('Alamat'),
            ExportColumn::make('dpp')
                ->label('DPP'),
            ExportColumn::make('ppn_amount')
                ->label('PPN'),
            ExportColumn::make('total_amount')
                ->label('Total'),
            ExportColumn::make('reference_number')
                ->label('Referensi'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('notes')
                ->label('Catatan'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor faktur pajak selesai. ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
