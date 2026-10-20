<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bukti konektivitas antar fitur: klik/transaksi di satu titik
 * tercatat di database dan terbaca di fitur lain (stok, laporan, approval).
 */
class FeatureConnectivityTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Outlet $outlet;

    protected PaymentMethod $pm;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->outlet = Outlet::create(['name' => 'Toko Pusat', 'code' => 'TP001', 'active' => true]);
        $this->owner->outlets()->attach($this->outlet->id);
        $this->pm = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
        $this->product = Product::create([
            'name' => 'Kopi Susu',
            'slug' => 'kopi-susu-' . uniqid(),
            'sku' => 'SKU' . strtoupper(substr(uniqid(), -6)),
            'barcode' => '899' . random_int(1000000000, 9999999999),
            'cost_price' => 10000,
            'selling_price' => 15000,
            'current_stock' => 100,
            'min_stock' => 5,
            'max_stock' => 200,
            'active' => true,
        ]);
    }

    public function test_checkout_masuk_database_dan_kurangi_stok(): void
    {
        $order = app(CheckoutService::class)->checkout([
            'outlet_id' => $this->outlet->id,
            'items' => [['product_id' => $this->product->id, 'quantity' => 2]],
            'payments' => [['payment_method_id' => $this->pm->id, 'amount' => 40000]],
            'use_tax' => false,
        ], $this->owner->id);

        // Order + item + pembayaran tercatat
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status' => 'completed']);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_id' => $this->product->id, 'quantity' => 2]);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id]);

        // Stok berkurang & ada jejak mutasi keluar
        $this->assertEquals(98, (int) $this->product->fresh()->current_stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => 2,
            'reference_type' => 'order',
            'reference_id' => $order->id,
        ]);
    }

    public function test_laporan_terbaca_dari_order_yang_sama(): void
    {
        $order = app(CheckoutService::class)->checkout([
            'outlet_id' => $this->outlet->id,
            'items' => [['product_id' => $this->product->id, 'quantity' => 2]],
            'payments' => [['payment_method_id' => $this->pm->id, 'amount' => 40000]],
            'use_tax' => false,
        ], $this->owner->id);

        // Agregat laporan = data order yang baru dibuat
        $this->assertEquals(
            (float) $order->total_amount,
            (float) Order::where('order_status', 'completed')->sum('total_amount')
        );
        $this->assertEquals(1, Order::where('order_status', 'completed')->count());
    }

    public function test_pembatalan_kembalikan_stok_dan_tercatat(): void
    {
        $order = app(CheckoutService::class)->checkout([
            'outlet_id' => $this->outlet->id,
            'items' => [['product_id' => $this->product->id, 'quantity' => 2]],
            'payments' => [['payment_method_id' => $this->pm->id, 'amount' => 40000]],
            'use_tax' => false,
        ], $this->owner->id);

        $this->assertEquals(98, (int) $this->product->fresh()->current_stock);

        $order->update(['order_status' => 'cancelled']);

        // Stok kembali + jejak mutasi masuk pembatalan
        $this->assertEquals(100, (int) $this->product->fresh()->current_stock);
        $this->assertDatabaseHas('stock_movements', [
            'reference_type' => 'order_cancel',
            'reference_id' => $order->id,
            'type' => 'in',
        ]);
        // Order batal tidak ikut laporan penjualan
        $this->assertEquals(0, Order::where('order_status', 'completed')->count());
    }

    public function test_approval_order_masuk_widget_dan_bisa_disetujui(): void
    {
        $order = Order::create([
            'order_number' => 'INV-TEST-00001',
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->owner->id,
            'subtotal' => 6000000,
            'total_amount' => 6000000,
            'payment_status' => 'pending',
            'order_status' => 'pending_approval',
        ]);

        $svc = app(ApprovalService::class);
        $pending = $svc->getPendingApprovals();
        $this->assertTrue($pending['orders']->contains('id', $order->id));

        $svc->approve($order, $this->owner->id, 'OK');

        $this->assertEquals('completed', $order->fresh()->order_status);
        $this->assertFalse($svc->getPendingApprovals()['orders']->contains('id', $order->id));
    }

    public function test_transaksi_customer_masuk_riwayat_dan_laporan(): void
    {
        $customer = Customer::create(['name' => 'Budi', 'phone' => '0812000001', 'active' => true]);

        $order = app(CheckoutService::class)->checkout([
            'outlet_id' => $this->outlet->id,
            'customer_id' => $customer->id,
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $this->pm->id, 'amount' => 20000]],
            'use_tax' => false,
        ], $this->owner->id);

        // Order terhubung ke customer & outlet — terbaca di laporan/fitur lain
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_id' => $customer->id,
            'outlet_id' => $this->outlet->id,
        ]);
        $this->assertEquals(1, Order::where('customer_id', $customer->id)->count());
        $this->assertEquals(1, StockMovement::where('product_id', $this->product->id)->count());
    }
}
