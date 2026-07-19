<?php

namespace App\Filament\Exports;

use App\Models\Customer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CustomerExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')->label('Nama'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('phone')->label('Telepon'),
            ExportColumn::make('address')->label('Alamat'),
            ExportColumn::make('customerGroup.name')->label('Grup'),
            ExportColumn::make('membershipTier.name')->label('Tier'),
            ExportColumn::make('total_points')->label('Total Poin'),
            ExportColumn::make('total_spent')->label('Total Belanja'),
            ExportColumn::make('active')->label('Aktif'),
            ExportColumn::make('created_at')->label('Terdaftar'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor pelanggan selesai. ' . number_format($export->successful_rows) . ' baris berhasil.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
