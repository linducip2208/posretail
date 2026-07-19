<?php

namespace App\Filament\Exports;

use App\Models\PriceChange;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PriceChangeExporter extends Exporter
{
    protected static ?string $model = PriceChange::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')
                ->label('Tanggal'),
            ExportColumn::make('product.name')
                ->label('Produk'),
            ExportColumn::make('old_cost_price')
                ->label('Harga Beli Lama'),
            ExportColumn::make('new_cost_price')
                ->label('Harga Beli Baru'),
            ExportColumn::make('old_selling_price')
                ->label('Harga Jual Lama'),
            ExportColumn::make('new_selling_price')
                ->label('Harga Jual Baru'),
            ExportColumn::make('changed_fields')
                ->label('Field Berubah'),
            ExportColumn::make('source')
                ->label('Sumber'),
            ExportColumn::make('user.name')
                ->label('Pengguna'),
            ExportColumn::make('notes')
                ->label('Catatan'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor riwayat harga selesai. ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
