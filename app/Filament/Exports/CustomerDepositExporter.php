<?php

namespace App\Filament\Exports;

use App\Models\CustomerDeposit;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CustomerDepositExporter extends Exporter
{
    protected static ?string $model = CustomerDeposit::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')->label('Tanggal'),
            ExportColumn::make('customer.name')->label('Pelanggan'),
            ExportColumn::make('outlet.name')->label('Outlet'),
            ExportColumn::make('type')->label('Tipe'),
            ExportColumn::make('amount')->label('Jumlah'),
            ExportColumn::make('balance_after')->label('Saldo Setelah'),
            ExportColumn::make('reference')->label('Referensi'),
            ExportColumn::make('user.name')->label('Dibuat Oleh'),
            ExportColumn::make('notes')->label('Catatan'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor deposit pelanggan selesai. ' . number_format($export->successful_rows) . ' baris berhasil.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
