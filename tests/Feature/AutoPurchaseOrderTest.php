<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoPurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $outlet = Outlet::create(['name' => 'T', 'code' => 'T01', 'active' => true]);
        User::factory()->create(['role' => 'owner']);
        Supplier::create(['name' => 'S', 'active' => true]);
        Product::create([
            'name' => 'Rendah', 'slug' => 'rendah-' . uniqid(), 'sku' => 'SKU' . strtoupper(substr(uniqid(), -6)),
            'barcode' => '899' . random_int(1000000000, 9999999999),
            'cost_price' => 5000, 'selling_price' => 8000,
            'current_stock' => 2, 'min_stock' => 10, 'max_stock' => 50,
            'outlet_id' => $outlet->id, 'active' => true,
        ]);
        Product::create([
            'name' => 'Aman', 'slug' => 'aman-' . uniqid(), 'sku' => 'SKU' . strtoupper(substr(uniqid(), -6)),
            'barcode' => '899' . random_int(1000000000, 9999999999),
            'cost_price' => 5000, 'selling_price' => 8000,
            'current_stock' => 100, 'min_stock' => 10, 'max_stock' => 200, 'active' => true,
        ]);
    }

    public function test_auto_po_buat_draft_sekali_dan_idempoten(): void
    {
        $this->artisan('pos:auto-po')->assertSuccessful();
        $this->assertEquals(1, PurchaseOrder::where('status', 'draft')->count());
        $po = PurchaseOrder::first();
        $this->assertEquals(1, $po->purchaseOrderItems()->count());
        $this->assertEquals(48 * 5000, (float) $po->fresh()->total_amount);

        // Jalan kedua di hari sama: tidak duplikat
        $this->artisan('pos:auto-po')->assertSuccessful();
        $this->assertEquals(1, PurchaseOrder::count());
    }

    public function test_auto_po_bisa_dimatikan(): void
    {
        SystemSetting::setValue('auto_po_enabled', '0');
        $this->artisan('pos:auto-po')->assertSuccessful();
        $this->assertEquals(0, PurchaseOrder::count());
    }
}
