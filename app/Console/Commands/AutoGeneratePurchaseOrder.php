<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoGeneratePurchaseOrder extends Command
{
    protected $signature = 'pos:auto-po {--force : Abaikan kunci idempotency harian}';

    protected $description = 'Buat draft PO otomatis tiap hari untuk produk di bawah stok minimum (idempoten)';

    public function handle(): int
    {
        if (! SystemSetting::getBool('auto_po_enabled', true)) {
            $this->warn('auto_po_enabled=false — dilewati. Aktifkan via Pengaturan Sistem.');

            return self::SUCCESS;
        }

        // Idempotency: satu PO otomatis per outlet per hari (kecuali --force).
        $today = now()->format('Y-m-d');

        $fallbackOutletId = DB::table('outlets')->where('active', true)->orderBy('id')->value('id');
        if (! $fallbackOutletId) {
            $this->warn('Tidak ada outlet aktif.');

            return self::SUCCESS;
        }

        $systemUserId = User::where('role', 'owner')->orderBy('id')->value('id')
            ?? User::orderBy('id')->value('id');
        if (! $systemUserId) {
            $this->warn('Tidak ada user untuk PO otomatis.');

            return self::SUCCESS;
        }

        $lowStock = Product::where('active', true)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->get();
        if ($lowStock->isEmpty()) {
            $this->info('Tidak ada produk di bawah stok minimum.');

            return self::SUCCESS;
        }

        // Produk yang sudah tercakup PO terbuka (draft/dipesan/dikirim) tidak dibuatkan lagi.
        $coveredProductIds = PurchaseOrderItem::whereHas('purchaseOrder', fn ($q) => $q->whereIn('status', ['draft', 'ordered', 'sent']))
            ->distinct()->pluck('product_id')->all();
        $lowStock = $lowStock->reject(fn ($p) => in_array($p->id, $coveredProductIds, true));
        if ($lowStock->isEmpty()) {
            $this->info('Semua produk stok rendah sudah tercakup PO terbuka.');

            return self::SUCCESS;
        }

        // Supplier per produk: riwayat PO terakhir, fallback supplier aktif pertama.
        $lastSupplierByProduct = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->whereIn('purchase_order_items.product_id', $lowStock->pluck('id'))
            ->selectRaw('purchase_order_items.product_id, MAX(purchase_orders.supplier_id) as supplier_id')
            ->groupBy('purchase_order_items.product_id')
            ->pluck('supplier_id', 'product_id');
        $defaultSupplierId = Supplier::where('active', true)->orderBy('id')->value('id');
        if (! $defaultSupplierId) {
            $this->warn('Tidak ada supplier aktif.');

            return self::SUCCESS;
        }

        // Kelompokkan per (outlet, supplier) agar PO realistis.
        $groups = [];
        foreach ($lowStock as $product) {
            $outletId = $product->outlet_id ?: $fallbackOutletId;
            $supplierId = $lastSupplierByProduct[$product->id] ?? $defaultSupplierId;
            $groups[$outletId . ':' . $supplierId][] = $product;
        }

        $poCount = 0;
        $itemCount = 0;
        foreach ($groups as $key => $products) {
            [$outletId, $supplierId] = explode(':', $key);

            if (! $this->option('force') && PurchaseOrder::whereDate('created_at', $today)
                    ->where('outlet_id', $outletId)
                    ->where('notes', 'like', 'Auto-generated:%')->exists()) {
                continue;
            }

            DB::transaction(function () use ($outletId, $supplierId, $products, $systemUserId, &$poCount, &$itemCount) {
                $po = PurchaseOrder::create([
                    'po_number' => PurchaseOrder::generateNumber(),
                    'supplier_id' => $supplierId,
                    'outlet_id' => $outletId,
                    'user_id' => $systemUserId,
                    'total_amount' => 0,
                    'status' => 'draft',
                    'notes' => 'Auto-generated: ' . count($products) . ' produk di bawah stok minimum',
                ]);

                $total = 0;
                foreach ($products as $product) {
                    $qty = (int) $product->max_stock - (int) $product->current_stock;
                    if ($qty <= 0) {
                        $qty = max(10, (int) $product->min_stock * 2);
                    }
                    $subtotal = $qty * (float) $product->cost_price;
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $product->cost_price,
                        'subtotal' => $subtotal,
                    ]);
                    $total += $subtotal;
                    $itemCount++;
                }
                $po->update(['total_amount' => $total]);
                $poCount++;
                $this->info("PO #{$po->po_number}: outlet #{$outletId}, supplier #{$supplierId}, " . count($products) . " produk, Rp " . number_format($total, 0, ',', '.'));
            });
        }

        if ($poCount > 0) {
            foreach (User::whereIn('role', ['owner', 'manager', 'admin', 'gudang'])->where('active', true)->get() as $user) {
                Notification::make()
                    ->title('PO Otomatis Dibuat')
                    ->body("{$poCount} draft PO otomatis untuk {$itemCount} produk stok rendah. Periksa sebelum dikirim ke supplier.")
                    ->warning()
                    ->sendToDatabase($user);
            }
        }

        $this->info("Done. {$poCount} PO(s) generated.");

        return self::SUCCESS;
    }
}
