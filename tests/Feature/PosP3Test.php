<?php

namespace Tests\Feature;

use App\Models\DiscountTemplate;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\PromoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PosP3Test extends TestCase
{
    use RefreshDatabase;

    protected function ctx(): array
    {
        $outlet = Outlet::create(['name' => 'Resto', 'code' => 'R1', 'active' => true]);
        $user = User::factory()->create(['role' => 'kasir']);
        $user->outlets()->attach($outlet->id);
        $pm = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
        $product = Product::create([
            'name' => 'Ayam Goreng', 'slug' => 'ayam-'.uniqid(), 'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'barcode' => '899'.random_int(1000000000, 9999999999),
            'cost_price' => 10000, 'selling_price' => 20000, 'current_stock' => 50,
            'active' => true, 'outlet_id' => $outlet->id,
        ]);

        return compact('outlet', 'user', 'pm', 'product');
    }

    public function test_bogo_promo_applies_free_item(): void
    {
        ['outlet' => $outlet, 'product' => $p] = $this->ctx();
        DiscountTemplate::create([
            'name' => 'Beli 2 Gratis 1', 'type' => 'buy_x_get_y', 'value' => 100,
            'buy_quantity' => 2, 'get_quantity' => 1, 'active' => true, 'outlet_id' => $outlet->id,
        ]);

        $svc = new PromoService;
        $res = $svc->bestDiscount($outlet->id, 80000, [
            ['product_id' => $p->id, 'quantity' => 4, 'unit_price' => 20000, 'subtotal' => 80000],
        ]);
        // 4 item: floor(4/3)*1 = 1 gratis → 20000
        $this->assertEquals(20000, (float) $res['discount']);
    }

    public function test_happy_hour_only_in_window(): void
    {
        ['outlet' => $outlet, 'product' => $p] = $this->ctx();
        DiscountTemplate::create([
            'name' => 'Happy Hour', 'type' => 'happy_hour', 'value' => 20,
            'happy_start' => '15:00:00', 'happy_end' => '17:00:00',
            'days' => '1,2,3,4,5', 'active' => true, 'outlet_id' => $outlet->id,
        ]);
        $svc = new PromoService;
        $lines = [['product_id' => $p->id, 'quantity' => 1, 'unit_price' => 20000, 'subtotal' => 20000]];

        $in = Carbon::parse('next Monday 16:00');
        $out = Carbon::parse('next Monday 18:00');
        $this->assertEquals(4000, (float) $svc->bestDiscount($outlet->id, 20000, $lines, $in)['discount']);
        $this->assertEquals(0, (float) $svc->bestDiscount($outlet->id, 20000, $lines, $out)['discount']);
    }

    public function test_checkout_auto_applies_promo_without_kitchen_ticket(): void
    {
        ['outlet' => $outlet, 'user' => $user, 'pm' => $pm, 'product' => $p] = $this->ctx();
        DiscountTemplate::create([
            'name' => 'Diskon 10%', 'type' => 'percent', 'value' => 10,
            'min_purchase' => 0, 'active' => true, 'outlet_id' => $outlet->id,
        ]);

        $order = app(CheckoutService::class)->checkout([
            'outlet_id' => $outlet->id,
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 20000]],
            'order_type' => 'walk_in',
            'use_tax' => false,
        ], $user->id);

        $this->assertEquals(2000, (float) $order->discount_amount);
        $this->assertEquals(18000, (float) $order->total_amount);
        $this->assertStringContainsString('promo', (string) $order->notes);
        // Ritel murni: tidak ada lagi tabel kitchen_tickets.
        $this->assertFalse(Schema::hasTable('kitchen_tickets'));
    }

    public function test_restaurant_routes_are_gone(): void
    {
        ['outlet' => $outlet, 'product' => $p] = $this->ctx();

        // QR self-order restoran sudah dihapus → 404.
        $res = $this->postJson("/menu/{$outlet->id}/order", [
            'items' => [['product_id' => $p->id, 'quantity' => 2]],
        ]);
        $res->assertNotFound();

        // Tabel restoran sudah tidak ada di database.
        $this->assertFalse(Schema::hasTable('tables'));
        $this->assertFalse(Schema::hasTable('table_areas'));
        $this->assertFalse(Schema::hasTable('reservations'));
    }

    public function test_forecast_api_returns_data(): void
    {
        ['outlet' => $outlet, 'user' => $user, 'pm' => $pm, 'product' => $p] = $this->ctx();
        app(CheckoutService::class)->checkout([
            'outlet_id' => $outlet->id,
            'items' => [['product_id' => $p->id, 'quantity' => 12]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 999999]],
            'use_tax' => false, 'skip_promo' => true,
        ], $user->id);

        $res = $this->actingAs($user)->getJson('/api/v1/inventory/forecast?weeks=12');
        $res->assertOk()->assertJsonStructure(['weeks', 'forecast', 'low_stock']);
    }
}
