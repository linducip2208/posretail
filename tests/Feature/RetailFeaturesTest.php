<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DiscountTemplate;
use App\Models\GiftCard;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Retur;
use App\Models\SerialNumber;
use App\Models\Supplier;
use App\Models\SupplierPayable;
use App\Models\SupplierReturn;
use App\Models\User;
use App\Services\TaxInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetailFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Outlet $outlet;

    protected Supplier $supplier;

    protected PaymentMethod $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'kasir']);
        $this->outlet = Outlet::create(['name' => 'Outlet Test', 'code' => 'OT001', 'active' => true]);
        $this->supplier = Supplier::create(['name' => 'Supplier Test', 'active' => true]);
        $this->paymentMethod = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);

        $this->user->outlets()->attach($this->outlet->id);
    }

    protected function makeProduct(int $stock = 100, array $extra = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Produk Test',
            'slug' => 'produk-test-'.uniqid(),
            'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'barcode' => '899'.random_int(1000000000, 9999999999),
            'cost_price' => 10000,
            'selling_price' => 15000,
            'current_stock' => $stock,
            'min_stock' => 5,
            'max_stock' => 100,
            'active' => true,
            'outlet_id' => $this->outlet->id,
        ], $extra));
    }

    // ─── 1. Serial Number / IMEI ───────────────────────

    public function test_serial_number_can_be_created_and_tracked(): void
    {
        $product = $this->makeProduct();

        $serial = SerialNumber::create([
            'product_id' => $product->id,
            'outlet_id' => $this->outlet->id,
            'serial_number' => 'IMEI123456789',
            'status' => 'in_stock',
        ]);

        $this->assertDatabaseHas('serial_numbers', [
            'serial_number' => 'IMEI123456789',
            'status' => 'in_stock',
        ]);

        $serial->update(['status' => 'sold']);

        $this->assertEquals('sold', $serial->fresh()->status);
        $this->assertEquals($product->id, $serial->product_id);
    }

    public function test_required_serial_product_forces_imei_at_checkout(): void
    {
        $product = $this->makeProduct(stock: 10, extra: ['serial_tracking' => 'required']);

        SerialNumber::create([
            'product_id' => $product->id,
            'outlet_id' => $this->outlet->id,
            'serial_number' => 'IMEI111111',
            'status' => 'in_stock',
        ]);

        $this->actingAs($this->user);

        // Tanpa IMEI → ditolak
        $response = $this->postJson('/pos/checkout', [
            'outlet_id' => $this->outlet->id,
            'items' => [
                ['id' => $product->id, 'qty' => 1, 'price' => 15000],
            ],
            'payment_method_id' => $this->paymentMethod->id,
            'paid_amount' => 15000,
            'use_tax' => false,
        ]);

        $response->assertStatus(422);

        // Dengan IMEI → sukses, serial jadi sold
        $response = $this->postJson('/pos/checkout', [
            'outlet_id' => $this->outlet->id,
            'items' => [
                ['id' => $product->id, 'qty' => 1, 'price' => 15000, 'serial_numbers' => ['IMEI111111']],
            ],
            'payment_method_id' => $this->paymentMethod->id,
            'paid_amount' => 15000,
            'use_tax' => false,
        ]);

        $response->assertOk();
        $this->assertEquals('sold', SerialNumber::where('serial_number', 'IMEI111111')->first()->status);
        $this->assertDatabaseHas('order_items', ['serial_number' => 'IMEI111111']);
    }

    public function test_invalid_serial_number_is_rejected_at_checkout(): void
    {
        $product = $this->makeProduct(stock: 10, extra: ['serial_tracking' => 'required']);

        $this->actingAs($this->user);

        $response = $this->postJson('/pos/checkout', [
            'outlet_id' => $this->outlet->id,
            'items' => [
                ['id' => $product->id, 'qty' => 1, 'price' => 15000, 'serial_numbers' => ['IMEI-TIDAK-ADA']],
            ],
            'payment_method_id' => $this->paymentMethod->id,
            'paid_amount' => 15000,
            'use_tax' => false,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
    }

    // ─── 2. Retur Supplier ─────────────────────────────

    public function test_supplier_return_reduces_stock_and_payable(): void
    {
        $product = $this->makeProduct(stock: 10);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-'.now()->format('Ymd').'-0001',
            'supplier_id' => $this->supplier->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->user->id,
            'total_amount' => 150000,
            'status' => 'received',
        ]);

        SupplierPayable::create([
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'invoice_number' => 'INV-001',
            'total_amount' => 150000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $return = SupplierReturn::create([
            'return_number' => 'RS-'.now()->format('Ymd').'-0001',
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->user->id,
            'total_amount' => 30000,
            'status' => 'draft',
        ]);

        $return->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 15000,
            'subtotal' => 30000,
        ]);

        $return->markReceived();

        $this->assertDatabaseHas('supplier_returns', ['status' => 'received']);
        $this->assertEquals(8, $product->fresh()->current_stock);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'reference_type' => 'supplier_return',
            'quantity' => 2,
        ]);

        $this->assertEquals(120000, (float) SupplierPayable::first()->total_amount);
    }

    public function test_supplier_return_item_auto_calculates_subtotal(): void
    {
        $product = $this->makeProduct(stock: 10);

        $return = SupplierReturn::create([
            'return_number' => 'RS-'.now()->format('Ymd').'-0002',
            'supplier_id' => $this->supplier->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->user->id,
            'total_amount' => 0,
            'status' => 'draft',
        ]);

        $return->items()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 20000,
            'subtotal' => 0,
        ]);

        $this->assertEquals(60000, (float) $return->items()->first()->subtotal);
        $this->assertEquals(60000, (float) $return->fresh()->total_amount);
    }

    // ─── 3. Voucher di Kasir ───────────────────────────

    public function test_voucher_code_applies_discount_at_checkout(): void
    {
        $product = $this->makeProduct(stock: 100);

        $voucher = GiftCard::create([
            'code' => 'VOUCHER10',
            'type' => 'discount_percent',
            'value' => 10,
            'min_purchase' => 0,
            'remaining_balance' => 0,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'status' => 'active',
            'max_usage' => 10,
            'used_count' => 0,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        $response = $this->postJson('/pos/checkout', [
            'outlet_id' => $this->outlet->id,
            'items' => [
                ['id' => $product->id, 'qty' => 2, 'price' => 15000],
            ],
            'payment_method_id' => $this->paymentMethod->id,
            'paid_amount' => 27000,
            'use_tax' => false,
            'voucher_code' => 'VOUCHER10',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $order = Order::first();
        $this->assertEquals(30000, (float) $order->subtotal);
        $this->assertEquals(3000, (float) $order->discount_amount);
        $this->assertEquals(27000, (float) $order->total_amount);

        $this->assertEquals(1, $voucher->fresh()->used_count);
        $this->assertDatabaseHas('gift_card_usages', ['gift_card_id' => $voucher->id, 'amount_used' => 3000]);
    }

    public function test_invalid_voucher_code_is_rejected(): void
    {
        $product = $this->makeProduct(stock: 100);

        $this->actingAs($this->user);

        $response = $this->postJson('/pos/checkout', [
            'outlet_id' => $this->outlet->id,
            'items' => [
                ['id' => $product->id, 'qty' => 1, 'price' => 15000],
            ],
            'payment_method_id' => $this->paymentMethod->id,
            'paid_amount' => 15000,
            'use_tax' => false,
            'voucher_code' => 'KODESALAH',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_validate_voucher_endpoint_returns_discount(): void
    {
        $this->makeProduct(stock: 10);

        GiftCard::create([
            'code' => 'DISC20',
            'type' => 'discount_percent',
            'value' => 20,
            'min_purchase' => 0,
            'remaining_balance' => 0,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'status' => 'active',
            'max_usage' => 10,
            'used_count' => 0,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        $response = $this->postJson('/api/pos/validate-voucher', [
            'code' => 'DISC20',
            'subtotal' => 50000,
        ]);

        $response->assertOk();
        $response->assertJson([
            'valid' => true,
            'discount' => 10000,
        ]);
    }

    // ─── 4. Promo Auto-Toggle ──────────────────────────

    public function test_promo_auto_toggle_command(): void
    {
        $this->makeProduct(stock: 10);

        DiscountTemplate::create([
            'name' => 'Promo Aktif',
            'type' => 'percentage',
            'value' => 10,
            'min_purchase' => 0,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'active' => false,
        ]);

        DiscountTemplate::create([
            'name' => 'Promo Lewat',
            'type' => 'percentage',
            'value' => 5,
            'min_purchase' => 0,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDay(),
            'active' => true,
        ]);

        $this->artisan('pos:update-promos')->assertSuccessful();

        $this->assertTrue((bool) DiscountTemplate::where('name', 'Promo Aktif')->first()->active);
        $this->assertFalse((bool) DiscountTemplate::where('name', 'Promo Lewat')->first()->active);
    }

    public function test_retur_completed_restores_stock_movement_and_journal(): void
    {
        $product = $this->makeProduct(stock: 5);

        $order = Order::create([
            'order_number' => 'ORD-'.now()->format('Ymd').'-TEST1',
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->user->id,
            'subtotal' => 30000,
            'total_amount' => 30000,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 15000,
            'subtotal' => 30000,
        ]);

        $retur = Retur::create([
            'order_id' => $order->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->user->id,
            'total_amount' => 0,
            'status' => 'pending',
        ]);

        $this->assertNotNull($retur->return_number);
        $this->assertEquals('customer_return', $retur->type);

        $retur->returnItems()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 15000,
            'subtotal' => 0,
        ]);

        $retur->refresh()->update(['status' => 'completed']);

        $this->assertEquals(7, $product->fresh()->current_stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'reference_type' => 'return',
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => 'return',
            'reference_id' => $retur->id,
        ]);
    }

    public function test_audit_log_written_on_order_cancel(): void
    {
        $product = $this->makeProduct(stock: 10);

        $this->actingAs($this->user);

        $order = Order::create([
            'order_number' => 'ORD-'.now()->format('Ymd').'-TEST2',
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->user->id,
            'subtotal' => 15000,
            'total_amount' => 15000,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);

        $order->update(['order_status' => 'cancelled']);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Order::class,
            'model_id' => $order->id,
            'action' => 'created',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Order::class,
            'model_id' => $order->id,
            'action' => 'updated',
        ]);
    }

    public function test_serial_sold_sets_warranty_expiry(): void
    {
        $product = $this->makeProduct(stock: 10, extra: [
            'serial_tracking' => 'required',
            'warranty_months' => 12,
        ]);

        SerialNumber::create([
            'product_id' => $product->id,
            'outlet_id' => $this->outlet->id,
            'serial_number' => 'IMEI-WARRANTY-1',
            'status' => 'in_stock',
        ]);

        $this->actingAs($this->user);

        $this->postJson('/pos/checkout', [
            'outlet_id' => $this->outlet->id,
            'items' => [
                ['id' => $product->id, 'qty' => 1, 'price' => 15000, 'serial_numbers' => ['IMEI-WARRANTY-1']],
            ],
            'payment_method_id' => $this->paymentMethod->id,
            'paid_amount' => 15000,
            'use_tax' => false,
        ])->assertOk();

        $serial = SerialNumber::where('serial_number', 'IMEI-WARRANTY-1')->first();

        $this->assertEquals('sold', $serial->status);
        $this->assertEquals(now()->addMonths(12)->toDateString(), $serial->warranty_expires_at->toDateString());
        $this->assertEquals('active', $serial->warrantyStatus());
    }

    public function test_receivables_csv_contains_partial_order(): void
    {
        $product = $this->makeProduct(stock: 10);

        Order::create([
            'order_number' => 'ORD-'.now()->format('Ymd').'-AR1',
            'customer_id' => null,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->user->id,
            'subtotal' => 50000,
            'total_amount' => 50000,
            'remaining_amount' => 30000,
            'payment_status' => 'partial',
            'order_status' => 'completed',
        ]);

        $this->actingAs($this->user);

        $response = $this->get('/export/laporan/piutang?outlet_id='.$this->outlet->id);

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('ORD-'.now()->format('Ymd').'-AR1', $response->getContent());
    }

    public function test_referral_awards_points_to_referrer_on_first_order(): void
    {
        $referrer = Customer::create([
            'name' => 'Referrer',
            'phone' => '081234567890',
            'active' => true,
        ]);

        $referred = Customer::create([
            'name' => 'Referred',
            'phone' => '081234567891',
            'referrer_id' => $referrer->id,
            'active' => true,
        ]);

        $this->assertNotNull($referrer->referral_code);

        $order = Order::create([
            'order_number' => 'ORD-'.now()->format('Ymd').'-REF1',
            'customer_id' => $referred->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->user->id,
            'subtotal' => 50000,
            'total_amount' => 50000,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);

        $this->assertDatabaseHas('loyalty_points', [
            'customer_id' => $referrer->id,
            'order_id' => $order->id,
            'points_earned' => 100,
        ]);

        $this->assertEquals(100, (int) $referrer->fresh()->total_points);
    }

    public function test_referral_not_awarded_twice(): void
    {
        $referrer = Customer::create(['name' => 'Referrer2', 'phone' => '081234567892', 'active' => true]);
        $referred = Customer::create(['name' => 'Referred2', 'phone' => '081234567893', 'referrer_id' => $referrer->id, 'active' => true]);

        Order::create([
            'order_number' => 'ORD-'.now()->format('Ymd').'-REF2A',
            'customer_id' => $referred->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->user->id,
            'subtotal' => 10000,
            'total_amount' => 10000,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);

        Order::create([
            'order_number' => 'ORD-'.now()->format('Ymd').'-REF2B',
            'customer_id' => $referred->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->user->id,
            'subtotal' => 20000,
            'total_amount' => 20000,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);

        $this->assertEquals(1, $referrer->fresh()->loyaltyPoints()->where('description', 'like', '%referral%')->count());
        $this->assertEquals(100, (int) $referrer->fresh()->total_points);
    }

    public function test_tax_invoice_generated_from_order(): void
    {
        $product = $this->makeProduct(stock: 10);

        $order = Order::create([
            'order_number' => 'ORD-'.now()->format('Ymd').'-TAX1',
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->user->id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 11000,
            'total_amount' => 111000,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);

        $this->actingAs($this->user);

        $invoice = TaxInvoiceService::generateFromOrder($order, [
            'customer_npwp' => '01.234.567.8-901.000',
        ]);

        $this->assertNotNull($invoice->invoice_number);
        $this->assertEquals(100000.0, (float) $invoice->dpp);
        $this->assertEquals(11000.0, (float) $invoice->ppn_amount);
        $this->assertDatabaseHas('tax_invoices', [
            'order_id' => $order->id,
            'customer_npwp' => '01.234.567.8-901.000',
        ]);
    }
}
