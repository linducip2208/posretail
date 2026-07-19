<?php

namespace App\Filament\Exports;

use App\Models\Delivery;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DeliveryExporter extends Exporter
{
    protected static ?string $model = Delivery::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('delivery_number')->label('No. Pengiriman'),
            ExportColumn::make('order.order_number')->label('No. Pesanan'),
            ExportColumn::make('recipient_name')->label('Penerima'),
            ExportColumn::make('recipient_phone')->label('Telepon Penerima'),
            ExportColumn::make('shipping_address')->label('Alamat'),
            ExportColumn::make('driver.name')->label('Driver'),
            ExportColumn::make('tracking_number')->label('No. Resi'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('delivery_notes')->label('Catatan'),
            ExportColumn::make('packed_at')->label('Dikemas'),
            ExportColumn::make('shipped_at')->label('Dikirim'),
            ExportColumn::make('delivered_at')->label('Terkirim'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor pengiriman selesai. ' . number_format($export->successful_rows) . ' baris berhasil.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
