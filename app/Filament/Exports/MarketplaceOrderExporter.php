<?php

namespace App\Filament\Exports;

use App\Models\MarketplaceOrder;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class MarketplaceOrderExporter extends Exporter
{
    protected static ?string $model = MarketplaceOrder::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('platform')->label('Platform'),
            ExportColumn::make('platform_order_id')->label('ID Pesanan'),
            ExportColumn::make('platform_invoice')->label('Invoice'),
            ExportColumn::make('customer_name')->label('Pelanggan'),
            ExportColumn::make('customer_phone')->label('Telepon'),
            ExportColumn::make('shipping_address')->label('Alamat Kirim'),
            ExportColumn::make('total_amount')->label('Total'),
            ExportColumn::make('shipping_fee')->label('Ongkir'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('order.order_number')->label('Order Lokal'),
            ExportColumn::make('notes')->label('Catatan'),
            ExportColumn::make('created_at')->label('Diterima'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor pesanan marketplace selesai. ' . number_format($export->successful_rows) . ' baris berhasil.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
