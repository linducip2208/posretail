<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileParityApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $kasir;

    protected Outlet $outlet;

    protected Outlet $other;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->kasir = User::factory()->create(['role' => 'kasir']);
        $this->outlet = Outlet::create(['name' => 'Toko Pusat', 'code' => 'TP001', 'active' => true]);
        $this->other = Outlet::create(['name' => 'Cabang', 'code' => 'CB002', 'active' => true]);
        foreach ([$this->owner, $this->kasir] as $u) {
            $u->outlets()->attach($this->outlet->id);
        }
        // Permission nyata via pivot (role string saja tidak cukup kecuali owner)
        $star = \App\Models\Permission::create(['name' => 'Super Admin', 'slug' => '*', 'group' => 'special']);
        $ownerRole = \App\Models\Role::create(['name' => 'Owner', 'slug' => 'owner', 'description' => 'test']);
        $ownerRole->permissions()->attach($star->id);
        $this->owner->roles()->attach($ownerRole->id);
        $ct = \App\Models\Permission::create(['name' => 'Buat Transaksi', 'slug' => 'create-transaksi', 'group' => 'transaksi']);
        $vt = \App\Models\Permission::create(['name' => 'Lihat Transaksi', 'slug' => 'view-transaksi', 'group' => 'transaksi']);
        $kasirRole = \App\Models\Role::create(['name' => 'Kasir', 'slug' => 'kasir', 'description' => 'test']);
        $kasirRole->permissions()->attach([$ct->id, $vt->id]);
        $this->kasir->roles()->attach($kasirRole->id);
        PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
        Supplier::create(['name' => 'Sup Tes', 'active' => true]);
        Product::create([
            'name' => 'Kopi', 'slug' => 'kopi-' . uniqid(), 'sku' => 'SKU' . strtoupper(substr(uniqid(), -6)),
            'barcode' => '899' . random_int(1000000000, 9999999999),
            'cost_price' => 10000, 'selling_price' => 15000,
            'current_stock' => 100, 'min_stock' => 5, 'max_stock' => 200, 'active' => true,
        ]);
    }

    public function test_probe_tanpa_token_401_untuk_semua_grup(): void
    {
        foreach (['purchase-orders', 'suppliers', 'stock-opnames', 'stock-transfers', 'stock-movements', 'returns', 'held-carts', 'discounts', 'installments', 'loyalty/rewards', 'raw-materials', 'users', 'roles', 'reports/sales', 'attendances/today', 'brands', 'units', 'expenses', 'shifts'] as $p) {
            $this->getJson("/api/v1/{$p}")->assertStatus(401);
        }
    }

    public function test_po_crud_dan_receive(): void
    {
        $this->actingAs($this->owner, 'api');
        $supplier = Supplier::first();
        $product = Product::first();

        $res = $this->postJson('/api/v1/purchase-orders', [
            'supplier_id' => $supplier->id, 'outlet_id' => $this->outlet->id,
            'items' => [['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 10000]],
        ])->assertCreated()->json('data');
        $this->assertEquals('draft', $res['status']);

        $this->getJson('/api/v1/purchase-orders')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/purchase-orders/' . $res['id'])->assertOk();

        $this->postJson('/api/v1/purchase-orders/' . $res['id'] . '/receive')->assertOk()
            ->assertJsonPath('data.status', 'received');
        $this->assertDatabaseHas('purchase_orders', ['id' => $res['id'], 'status' => 'received']);

        // Kasir tidak boleh (butuh permission pembelian)
        $this->actingAs($this->kasir, 'api');
        $this->postJson('/api/v1/purchase-orders', [
            'supplier_id' => $supplier->id, 'outlet_id' => $this->outlet->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10000]],
        ])->assertForbidden();
    }

    public function test_supplier_outlet_asing_ditolak(): void
    {
        $this->actingAs($this->owner, 'api');
        $this->getJson('/api/v1/suppliers')->assertOk();

        // Kasir hanya punya outlet 1 → outlet lain wajib 403
        $this->actingAs($this->kasir, 'api');
        $this->getJson('/api/v1/purchase-orders?outlet_id=' . $this->other->id)->assertForbidden();
    }

    public function test_opname_dan_approve_koreksi_stok(): void
    {
        $this->actingAs($this->owner, 'api');
        $product = Product::first();

        $res = $this->postJson('/api/v1/stock-opnames', [
            'outlet_id' => $this->outlet->id,
            'items' => [['product_id' => $product->id, 'actual_stock' => 90]],
        ])->assertCreated()->json('data');
        $this->assertEquals('pending', $res['status']);
        $this->getJson('/api/v1/stock-opnames')->assertOk();

        $this->postJson('/api/v1/stock-opnames/' . $res['id'] . '/approve')->assertOk()
            ->assertJsonPath('data.status', 'completed');
        $this->assertEquals(90, (int) $product->fresh()->current_stock);
        $this->assertDatabaseHas('stock_movements', ['reference_type' => 'opname', 'reference_id' => $res['id']]);
    }

    public function test_transfer_kirim_terima_dan_jejak_mutasi(): void
    {
        $this->actingAs($this->owner, 'api');
        // Owner perlu akses outlet tujuan juga
        $this->owner->outlets()->attach($this->other->id);
        $product = Product::first();

        $res = $this->postJson('/api/v1/stock-transfers', [
            'from_outlet_id' => $this->outlet->id, 'to_outlet_id' => $this->other->id,
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ])->assertCreated()->json('data');

        $this->postJson('/api/v1/stock-transfers/' . $res['id'] . '/send')->assertOk()
            ->assertJsonPath('data.status', 'sent');
        $this->postJson('/api/v1/stock-transfers/' . $res['id'] . '/receive')->assertOk()
            ->assertJsonPath('data.status', 'received');
        $this->assertDatabaseHas('stock_movements', ['reference_type' => 'transfer', 'type' => 'out']);
        $this->assertDatabaseHas('stock_movements', ['reference_type' => 'transfer', 'type' => 'in']);
        $this->getJson('/api/v1/stock-movements')->assertOk();
    }

    public function test_retur_dan_approve_kembalikan_stok(): void
    {
        $this->actingAs($this->owner, 'api');
        $product = Product::first();
        $order = \App\Models\Order::create([
            'order_number' => 'INV-T-00001', 'outlet_id' => $this->outlet->id,
            'user_id' => $this->owner->id, 'subtotal' => 30000, 'total_amount' => 33300,
            'payment_status' => 'paid', 'order_status' => 'completed',
        ]);
        \App\Models\OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 15000, 'subtotal' => 30000]);

        $res = $this->postJson('/api/v1/returns', [
            'order_id' => $order->id, 'reason' => 'Rusak',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertCreated()->json('data');

        $this->postJson('/api/v1/returns/' . $res['id'] . '/approve')->assertOk();
        $this->assertEquals(101, (int) $product->fresh()->current_stock);
    }

    public function test_held_cart_discount_loyalty_raw_attendance(): void
    {
        $this->actingAs($this->kasir, 'api');

        $cart = $this->postJson('/api/v1/held-carts', [
            'outlet_id' => $this->outlet->id, 'label' => 'Tunggu',
            'items' => [['product_id' => Product::first()->id, 'qty' => 2]],
        ])->assertCreated()->json('data');
        $this->getJson('/api/v1/held-carts')->assertOk();
        $this->postJson('/api/v1/held-carts/' . $cart['id'] . '/resume')->assertOk();
        $this->deleteJson('/api/v1/held-carts/' . $cart['id'])->assertOk();

        $this->getJson('/api/v1/discounts')->assertOk();
        $this->getJson('/api/v1/installments')->assertOk();
        $this->getJson('/api/v1/loyalty/rewards')->assertOk();

        $customer = Customer::create(['name' => 'Budi', 'phone' => '0811', 'active' => true, 'total_points' => 1000]);
        \App\Models\LoyaltyReward::create(['name' => 'Diskon', 'points_required' => 100, 'discount_type' => 'fixed', 'discount_value' => 5000, 'active' => true]);
        $this->getJson('/api/v1/loyalty/summary?customer_id=' . $customer->id)->assertOk()
            ->assertJsonPath('data.total_points', 1000);

        $this->postJson('/api/v1/attendances/clock-in', ['outlet_id' => $this->outlet->id])->assertCreated();
        $today = $this->getJson('/api/v1/attendances/today')->assertOk()->json('data');
        $this->assertContains($today['status'], ['present', 'late']);

        $pm = PaymentMethod::first();
        $unit = \App\Models\Unit::create(['name' => 'Kg', 'code' => 'KG']);
        \App\Models\RawMaterial::create(['name' => 'Gula', 'code' => 'RM001', 'outlet_id' => $this->outlet->id, 'unit_id' => $unit->id, 'current_stock' => 10, 'min_stock' => 2]);
        // Bahan baku: kasir tidak punya akses inventori → 403 sesuai matrix
        $this->getJson('/api/v1/raw-materials')->assertForbidden();
        $this->actingAs($this->owner, 'api');
        $this->getJson('/api/v1/raw-materials')->assertOk();
    }

    public function test_users_roles_hanya_owner_manager(): void
    {
        $this->actingAs($this->owner, 'api');
        $this->getJson('/api/v1/users')->assertOk();
        $this->getJson('/api/v1/roles')->assertOk();

        $this->actingAs($this->kasir, 'api');
        $this->getJson('/api/v1/users')->assertForbidden();
        $this->getJson('/api/v1/roles')->assertForbidden();
    }

    public function test_reports_dan_batas_93_hari(): void
    {
        $this->actingAs($this->owner, 'api');
        $this->getJson('/api/v1/reports/sales')->assertOk()->assertJsonStructure(['data' => ['revenue', 'transactions', 'top_products']]);
        $this->getJson('/api/v1/reports/stock')->assertOk()->assertJsonStructure(['data' => ['inventory_value', 'low_stock']]);
        $this->getJson('/api/v1/reports/finance')->assertOk()->assertJsonStructure(['data' => ['income', 'expense', 'net']]);

        $this->getJson('/api/v1/reports/sales?start_date=2026-01-01&end_date=2026-12-31')->assertStatus(422);

        $this->actingAs($this->kasir, 'api');
        $this->getJson('/api/v1/reports/sales')->assertForbidden();
    }

    public function test_katalog_pelengkap_dan_expense_dan_shift(): void
    {
        \App\Models\Brand::create(['name' => 'Indofood', 'slug' => 'indofood', 'active' => true]);
        \App\Models\Unit::create(['name' => 'Pcs', 'code' => 'PCS']);

        $this->actingAs($this->kasir, 'api');
        $this->getJson('/api/v1/brands')->assertOk();
        $this->getJson('/api/v1/units')->assertOk()->assertJsonPath('data.0.code', 'PCS');
        $this->getJson('/api/v1/shifts')->assertOk();

        // Kasir tanpa akses finance → 403
        $this->getJson('/api/v1/expenses')->assertForbidden();

        $this->actingAs($this->owner, 'api');
        $this->getJson('/api/v1/expenses')->assertOk();
        $this->postJson('/api/v1/expenses', [
            'outlet_id' => $this->outlet->id, 'category' => 'Listrik', 'amount' => 250000,
        ])->assertCreated();
        $this->assertDatabaseHas('expenses', ['category' => 'Listrik', 'amount' => 250000]);
    }
}
