<?php

namespace App\Filament\Exports;

use App\Models\GiftCard;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class GiftCardExporter extends Exporter
{
    protected static ?string $model = GiftCard::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('code')
                ->label('Kode'),
            ExportColumn::make('type')
                ->label('Tipe'),
            ExportColumn::make('value')
                ->label('Nilai'),
            ExportColumn::make('min_purchase')
                ->label('Min. Pembelian'),
            ExportColumn::make('remaining_balance')
                ->label('Sisa Saldo'),
            ExportColumn::make('valid_from')
                ->label('Berlaku Dari'),
            ExportColumn::make('valid_until')
                ->label('Berlaku Sampai'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('max_usage')
                ->label('Maks. Pakai'),
            ExportColumn::make('used_count')
                ->label('Terpakai'),
            ExportColumn::make('customer.name')
                ->label('Pelanggan'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor voucher selesai. ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
