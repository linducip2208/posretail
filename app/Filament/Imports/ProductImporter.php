<?php

namespace App\Filament\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Unit;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->label('Nama Produk'),

            ImportColumn::make('category')
                ->requiredMapping()
                ->rules(['required', 'string'])
                ->label('Kategori')
                ->fillRecordUsing(function (Product $record, string $state): void {
                    $category = Category::firstOrCreate(
                        ['name' => trim($state)],
                        ['slug' => \Illuminate\Support\Str::slug(trim($state)), 'active' => true]
                    );
                    $record->category_id = $category->id;
                }),

            ImportColumn::make('brand')
                ->label('Brand')
                ->fillRecordUsing(function (Product $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }
                    $brand = Brand::firstOrCreate(
                        ['name' => trim($state)],
                        ['slug' => \Illuminate\Support\Str::slug(trim($state))]
                    );
                    $record->brand_id = $brand->id;
                }),

            ImportColumn::make('unit')
                ->requiredMapping()
                ->rules(['required', 'string'])
                ->label('Satuan')
                ->fillRecordUsing(function (Product $record, string $state): void {
                    $unit = Unit::firstOrCreate(
                        ['name' => trim($state)],
                        ['slug' => \Illuminate\Support\Str::slug(trim($state))]
                    );
                    $record->unit_id = $unit->id;
                }),

            ImportColumn::make('outlet')
                ->label('Outlet')
                ->fillRecordUsing(function (Product $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }
                    $outlet = Outlet::where('name', trim($state))->first();
                    if ($outlet) {
                        $record->outlet_id = $outlet->id;
                    }
                }),

            ImportColumn::make('sku')
                ->label('SKU')
                ->rules(['nullable', 'string', 'max:50']),

            ImportColumn::make('barcode')
                ->label('Barcode')
                ->rules(['nullable', 'string', 'max:50']),

            ImportColumn::make('cost_price')
                ->label('Harga Beli')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('selling_price')
                ->label('Harga Jual')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('wholesale_price')
                ->label('Harga Grosir')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('member_price')
                ->label('Harga Member')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('current_stock')
                ->label('Stok Saat Ini')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0']),

            ImportColumn::make('min_stock')
                ->label('Stok Minimum')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0']),

            ImportColumn::make('max_stock')
                ->label('Stok Maksimum')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0']),

            ImportColumn::make('active')
                ->label('Aktif (1/0)')
                ->boolean()
                ->rules(['nullable', 'boolean']),

            ImportColumn::make('description')
                ->label('Deskripsi')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?Product
    {
        $barcode = $this->data['barcode'] ?? null;
        if ($barcode) {
            return Product::firstOrNew(['barcode' => $barcode]);
        }

        $sku = $this->data['sku'] ?? null;
        if ($sku) {
            return Product::firstOrNew(['sku' => $sku]);
        }

        return new Product;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import produk selesai. ' . number_format($import->successful_rows) . ' produk berhasil diimport.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diimport.';
        }

        return $body;
    }
}
