<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('sku')
                ->label('SKU'),
            ExportColumn::make('barcode')
                ->label('Barcode'),
            ExportColumn::make('name')
                ->label('Nama Produk'),
            ExportColumn::make('category.name')
                ->label('Kategori'),
            ExportColumn::make('brand.name')
                ->label('Brand'),
            ExportColumn::make('unit.name')
                ->label('Satuan'),
            ExportColumn::make('cost_price')
                ->label('Harga Beli'),
            ExportColumn::make('selling_price')
                ->label('Harga Jual'),
            ExportColumn::make('wholesale_price')
                ->label('Harga Grosir'),
            ExportColumn::make('member_price')
                ->label('Harga Member'),
            ExportColumn::make('current_stock')
                ->label('Stok Saat Ini'),
            ExportColumn::make('min_stock')
                ->label('Stok Minimum'),
            ExportColumn::make('max_stock')
                ->label('Stok Maksimum'),
            ExportColumn::make('active')
                ->label('Aktif'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor produk selesai. ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
