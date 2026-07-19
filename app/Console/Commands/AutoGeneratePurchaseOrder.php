<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class AutoGeneratePurchaseOrder extends Command
{
    protected $signature = 'pos:auto-po';

    protected $description = 'Auto-generate PO for products below minimum stock';

    public function handle(): int
    {
        $lowStockProducts = Product::where('active', true)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->get()
            ->groupBy('outlet_id');

        $poCount = 0;

        foreach ($lowStockProducts as $outletId => $products) {
            $supplier = Supplier::first();
            if (!$supplier) {
                $this->warn("No supplier found, skipping outlet #{$outletId}");
                continue;
            }

            $po = PurchaseOrder::create([
                'supplier_id' => $supplier->id,
                'outlet_id' => $outletId,
                'user_id' => User::where('role', 'admin')->first()?->id,
                'po_number' => 'PO-AUTO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
                'status' => 'draft',
                'total_amount' => 0,
                'notes' => 'Auto-generated: ' . $products->count() . ' produk di bawah stok minimum',
            ]);

            $total = 0;
            foreach ($products as $product) {
                $qty = $product->max_stock - $product->current_stock;
                if ($qty <= 0) {
                    $qty = max(10, $product->min_stock * 2);
                }

                $po->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->cost_price,
                    'subtotal' => $qty * $product->cost_price,
                ]);
                $total += $qty * $product->cost_price;
            }

            $po->update(['total_amount' => $total]);
            $poCount++;

            $this->info("PO #{$po->po_number} created for outlet #{$outletId}: {$products->count()} products, total Rp " . number_format($total, 0, ',', '.'));
        }

        if ($poCount > 0) {
            $recipients = User::whereIn('role', ['owner', 'manager', 'admin', 'gudang'])->get();
            foreach ($recipients as $user) {
                Notification::make()
                    ->title('PO Otomatis Dibuat')
                    ->body("{$poCount} purchase order otomatis dibuat untuk {$lowStockProducts->flatten()->count()} produk stok rendah.")
                    ->warning()
                    ->sendToDatabase($user);
            }
        }

        $this->info("Done. {$poCount} PO(s) generated.");

        return self::SUCCESS;
    }
}
