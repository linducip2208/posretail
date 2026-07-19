<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

class LaporanStok extends Page
{
    protected static string|UnitEnum|null $navigationGroup = '📈 Laporan';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $title = 'Laporan Stok';

    protected string $view = 'filament.pages.laporan-stok';

    public ?int $outletId = null;

    public function mount(): void
    {
    }

    public function getOutletsProperty()
    {
        return auth()->user()?->accessibleOutlets()?->get() ?? Outlet::where('active', true)->orderBy('name')->get();
    }

    public function getTotalStockValueProperty()
    {
        return (float) $this->productBaseQuery()
            ->selectRaw('SUM(current_stock * cost_price) as total_value')
            ->value('total_value');
    }

    public function getTotalProductsProperty()
    {
        return $this->productBaseQuery()->count();
    }

    public function getLowStockCountProperty()
    {
        return $this->productBaseQuery()
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->count();
    }

    public function getLowStockProductsProperty()
    {
        return $this->productBaseQuery()
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->orderBy('current_stock')
            ->limit(15)
            ->get();
    }

    public function getStockMovementsProperty()
    {
        return StockMovement::with('product:id,name', 'outlet:id,name')
            ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId))
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    public function getTopStockedProductsProperty()
    {
        return $this->productBaseQuery()
            ->orderByDesc('current_stock')
            ->limit(10)
            ->get();
    }

    public function getCategoryLabelsProperty(): array
    {
        return DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->when($this->outletId, fn ($q) => $q->where('products.outlet_id', $this->outletId))
            ->where('products.active', true)
            ->selectRaw('categories.name, SUM(products.current_stock * products.cost_price) as total_value')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_value')
            ->pluck('categories.name')
            ->toArray();
    }

    public function getCategoryDataProperty(): array
    {
        return DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->when($this->outletId, fn ($q) => $q->where('products.outlet_id', $this->outletId))
            ->where('products.active', true)
            ->selectRaw('categories.name, SUM(products.current_stock * products.cost_price) as total_value')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_value')
            ->pluck('total_value')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    protected function productBaseQuery()
    {
        return Product::where('active', true)
            ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId));
    }
}
