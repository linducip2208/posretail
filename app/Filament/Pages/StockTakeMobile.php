<?php

namespace App\Filament\Pages;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class StockTakeMobile extends Page
{
    protected static string|UnitEnum|null $navigationGroup = '📦 Inventory';

    protected static ?int $navigationSort = 17;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $title = 'Stock Take (Mobile)';

    protected string $view = 'filament.pages.stock-take-mobile';

    public ?int $outletId = null;

    public ?string $search = null;

    public array $selectedProducts = [];

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->hasPermission('*')) {
            $this->outletId = Outlet::where('active', true)->orderBy('name')->value('id');
        } else {
            $this->outletId = $user->outlets()->where('active', true)->orderBy('name')->value('id');
        }
    }

    public function getOutletsProperty()
    {
        return auth()->user()?->accessibleOutlets()?->get() ?? Outlet::where('active', true)->orderBy('name')->get();
    }

    public function getProductsProperty()
    {
        $query = Product::with(['category', 'unit'])
            ->where('active', true)
            ->when($this->outletId, fn ($q) => $q->where('outlet_id', $this->outletId))
            ->when($this->search, fn ($q) => $q->where(function ($sub) {
                $sub->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', 'like', '%' . $this->search . '%');
            }))
            ->orderBy('name')
            ->limit(50)
            ->get();

        return $query;
    }

    public function updatedOutletId(): void
    {
        $this->selectedProducts = [];
    }

    public function completeStockTake(): void
    {
        if (empty($this->selectedProducts)) {
            return;
        }

        DB::transaction(function () {
            $prefix = 'SO-' . Carbon::now()->format('Ymd') . '-';
            $last = StockOpname::where('opname_number', 'like', $prefix . '%')
                ->orderByRaw('LENGTH(opname_number) DESC, opname_number DESC')
                ->first();
            $num = $last ? (int) substr($last->opname_number, strlen($prefix)) + 1 : 1;
            $opnameNumber = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);

            $stockOpname = StockOpname::create([
                'opname_number' => $opnameNumber,
                'outlet_id' => $this->outletId,
                'user_id' => auth()->id(),
                'status' => 'completed',
                'notes' => 'Stock take via mobile pada ' . Carbon::now()->format('d/m/Y H:i'),
            ]);

            foreach ($this->selectedProducts as $productId => $data) {
                if (!isset($data['actual_qty']) || $data['actual_qty'] === null) {
                    continue;
                }

                $systemQty = (int) ($data['system_qty'] ?? 0);
                $actualQty = (int) $data['actual_qty'];
                $difference = $actualQty - $systemQty;

                StockOpnameItem::create([
                    'stock_opname_id' => $stockOpname->id,
                    'product_id' => $productId,
                    'system_stock' => $systemQty,
                    'actual_stock' => $actualQty,
                    'difference' => $difference,
                    'notes' => 'Stock take mobile',
                ]);

                if ($difference !== 0) {
                    $product = Product::find($productId);
                    if ($product) {
                        $product->update(['current_stock' => $actualQty]);
                    }
                }
            }
        });

        Notification::make()
            ->title('Stock take selesai')
            ->body(count($this->selectedProducts) . ' produk berhasil dicatat.')
            ->success()
            ->send();

        $this->selectedProducts = [];
        $this->search = null;
    }
}
