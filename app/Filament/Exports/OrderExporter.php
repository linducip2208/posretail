<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('order_number')
                ->label('No. Order'),
            ExportColumn::make('created_at')
                ->label('Tanggal'),
            ExportColumn::make('outlet.name')
                ->label('Outlet'),
            ExportColumn::make('user.name')
                ->label('Kasir'),
            ExportColumn::make('customer.name')
                ->label('Pelanggan'),
            ExportColumn::make('subtotal')
                ->label('Subtotal'),
            ExportColumn::make('discount_amount')
                ->label('Diskon'),
            ExportColumn::make('tax_amount')
                ->label('Pajak'),
            ExportColumn::make('total_amount')
                ->label('Total'),
            ExportColumn::make('payment_status')
                ->label('Status Bayar'),
            ExportColumn::make('order_status')
                ->label('Status Order'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor pesanan selesai. ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
