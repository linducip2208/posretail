<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class CetakLabelBarcode extends Page
{
    protected static string|UnitEnum|null $navigationGroup = '📦 Inventory';

    protected static ?int $navigationSort = 9;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $title = 'Cetak Label Barcode';

    protected string $view = 'filament.pages.cetak-label-barcode';

    public string $search = '';

    public array $selectedProducts = [];

    public array $quantities = [];

    public function mount(): void
    {
    }

    public function getProductsProperty()
    {
        if (blank($this->search) || strlen($this->search) < 2) {
            return collect();
        }

        return Product::with(['variants', 'category', 'unit'])
            ->where('active', true)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->limit(30)
            ->get();
    }

    public function getSelectedItemsProperty(): array
    {
        $items = [];

        foreach ($this->selectedProducts as $key) {
            $parts = explode(':', (string) $key);
            $type = $parts[0] ?? '';
            $id = $parts[1] ?? '';

            if ($type === 'product') {
                $product = Product::find($id);
                if ($product && ! empty($product->barcode)) {
                    $qty = $this->quantities[$key] ?? 1;
                    for ($i = 0; $i < max(1, (int) $qty); $i++) {
                        $items[] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'barcode' => $product->barcode,
                            'sku' => $product->sku,
                            'price' => $product->selling_price,
                            'type' => 'product',
                        ];
                    }
                }
            } elseif ($type === 'variant') {
                $variant = ProductVariant::with('product')->find($id);
                if ($variant && ! empty($variant->barcode)) {
                    $qty = $this->quantities[$key] ?? 1;
                    for ($i = 0; $i < max(1, (int) $qty); $i++) {
                        $items[] = [
                            'id' => $variant->id,
                            'name' => $variant->product->name.' — '.$variant->name,
                            'barcode' => $variant->barcode,
                            'sku' => $variant->sku,
                            'price' => $variant->selling_price,
                            'type' => 'variant',
                        ];
                    }
                }
            }
        }

        return $items;
    }

    public function toggleProduct(string $key): void
    {
        if (in_array($key, $this->selectedProducts)) {
            $this->selectedProducts = array_values(array_filter(
                $this->selectedProducts,
                fn ($v) => $v !== $key
            ));
            unset($this->quantities[$key]);
        } else {
            $this->selectedProducts[] = $key;
            $this->quantities[$key] = 1;
        }
    }

    public function removeProduct(string $key): void
    {
        $this->selectedProducts = array_values(array_filter(
            $this->selectedProducts,
            fn ($v) => $v !== $key
        ));
        unset($this->quantities[$key]);
    }

    public function updatedSearch(): void
    {
    }

    public function printLabels(): array
    {
        return $this->getSelectedItemsProperty();
    }
}
