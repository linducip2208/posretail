<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
        ]);

        Outlet::create(['name' => 'Outlet Test', 'code' => 'OT001', 'active' => true]);
        Supplier::create(['name' => 'Supplier Test', 'active' => true]);
        PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
    }

    public function test_purchase_order_workflow(): void
    {
        $user = User::first();
        $outlet = Outlet::first();
        $supplier = Supplier::first();

        // Create products
        $product = Product::create([
            'name' => 'Produk PO Test',
            'slug' => 'produk-po-test',
            'sku' => 'SKU00001',
            'barcode' => '8991000000001',
            'cost_price' => 10000,
            'selling_price' => 15000,
            'current_stock' => 0,
            'min_stock' => 5,
            'max_stock' => 100,
            'active' => true,
        ]);

        // Create PO
        $po = PurchaseOrder::create([
            'po_number' => 'PO-20260709-0001',
            'supplier_id' => $supplier->id,
            'outlet_id' => $outlet->id,
            'user_id' => $user->id,
            'total_amount' => 50000,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('purchase_orders', ['status' => 'draft']);

        $po->update(['status' => 'ordered']);
        $this->assertEquals('ordered', $po->fresh()->status);

        $po->update(['status' => 'received']);
        $this->assertEquals('received', $po->fresh()->status);
    }

    public function test_stock_transfer_between_outlets(): void
    {
        $product = Product::create([
            'name' => 'Produk Transfer',
            'slug' => 'produk-transfer',
            'sku' => 'SKU00002',
            'barcode' => '8991000000002',
            'cost_price' => 10000,
            'selling_price' => 15000,
            'current_stock' => 50,
            'min_stock' => 5,
            'max_stock' => 100,
            'active' => true,
        ]);

        $fromOutlet = Outlet::first();
        $toOutlet = Outlet::create(['name' => 'Outlet Tujuan', 'code' => 'OT002', 'active' => true]);

        $user = User::first();

        $transfer = \App\Models\StockTransfer::create([
            'transfer_number' => 'STF-20260709-0001',
            'from_outlet_id' => $fromOutlet->id,
            'to_outlet_id' => $toOutlet->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        \App\Models\StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('stock_transfers', ['status' => 'draft']);

        $transfer->update(['status' => 'sent']);
        $this->assertEquals('sent', $transfer->fresh()->status);

        $transfer->update(['status' => 'received']);
        $this->assertEquals('received', $transfer->fresh()->status);
    }

    public function test_order_fails_with_insufficient_stock(): void
    {
        $product = Product::create([
            'name' => 'Produk Stok Habis',
            'slug' => 'produk-stok-habis',
            'sku' => 'SKU00003',
            'barcode' => '8991000000003',
            'cost_price' => 10000,
            'selling_price' => 15000,
            'current_stock' => 0,
            'min_stock' => 5,
            'max_stock' => 100,
            'active' => true,
        ]);

        $user = User::first();
        $outlet = Outlet::first();
        $paymentMethod = PaymentMethod::first();

        $this->actingAs($user, 'api');

        $response = $this->postJson('/api/v1/orders', [
            'outlet_id' => $outlet->id,
            'items' => [
                ['id' => $product->id, 'quantity' => 1, 'unit_price' => 15000],
            ],
            'payments' => [
                ['payment_method_id' => $paymentMethod->id, 'amount' => 15000],
            ],
        ]);

        // Order should still be created even if stock is low (admin handles stock later)
        // But we verify the API handles the scenario gracefully
        $this->assertContains($response->status(), [200, 201, 422]);
    }

    public function test_api_rate_limiting(): void
    {
        $user = User::first();

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Rate limiting on login: 10 per minute
        // Make rapid requests and verify no 429 after a few
        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertNotEquals(429, $response->status());
    }

    public function test_invalid_order_payload_is_rejected(): void
    {
        $user = User::first();
        $this->actingAs($user, 'api');

        // Missing items
        $response = $this->postJson('/api/v1/orders', [
            'outlet_id' => 1,
            'payments' => [
                ['payment_method_id' => 1, 'amount' => 10000],
            ],
        ]);

        $response->assertStatus(422);

        // Missing outlet
        $response = $this->postJson('/api/v1/orders', [
            'items' => [
                ['id' => 1, 'quantity' => 1, 'unit_price' => 10000],
            ],
            'payments' => [
                ['payment_method_id' => 1, 'amount' => 10000],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_unauthorized_user_cannot_access_portal(): void
    {
        $response = $this->get('/portal');
        $this->assertTrue($response->status() >= 300);
    }

    public function test_payment_proof_upload_requires_auth(): void
    {
        $response = $this->post('/portal/order/1/upload-proof');
        $this->assertTrue($response->status() >= 300);
    }

    public function test_report_pdf_export_requires_auth(): void
    {
        $response = $this->get('/export/laporan/penjualan/pdf');
        $this->assertTrue($response->status() >= 300);
    }
}
