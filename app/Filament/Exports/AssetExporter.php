<?php

namespace App\Filament\Exports;

use App\Models\CompanyAsset;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AssetExporter extends Exporter
{
    protected static ?string $model = CompanyAsset::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('asset_code')->label('Kode Aset'),
            ExportColumn::make('name')->label('Nama Aset'),
            ExportColumn::make('outlet.name')->label('Outlet'),
            ExportColumn::make('category')->label('Kategori'),
            ExportColumn::make('purchase_date')->label('Tanggal Pembelian'),
            ExportColumn::make('purchase_value')->label('Nilai Pembelian'),
            ExportColumn::make('current_value')->label('Nilai Saat Ini'),
            ExportColumn::make('salvage_value')->label('Nilai Residu'),
            ExportColumn::make('useful_life_months')->label('Masa Manfaat (Bulan)'),
            ExportColumn::make('monthly_depreciation')->label('Penyusutan Bulanan'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('location')->label('Lokasi'),
            ExportColumn::make('assignedUser.name')->label('Ditugaskan Ke'),
            ExportColumn::make('notes')->label('Catatan'),
            ExportColumn::make('created_at')->label('Tanggal Dibuat'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor aset selesai. ' . number_format($export->successful_rows) . ' baris berhasil.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
