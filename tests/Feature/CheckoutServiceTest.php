<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function makeContext(): array
    {
        $outlet = Outlet::create(['name' => 'Outlet POS', 'code' => 'POS1', 'active' => true]);
        $user = User::factory()->create(['role' => 'kasir']);
        $user->outlets()->attach($outlet->id);
        $pm = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
        $product = Product::create([
            'name' => 'Kopi Susu',
            'slug' => 'kopi-susu-'.uniqid(),
            'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'barcode' => '899'.random_int(1000000000, 9999999999),
            'cost_price' => 10000,
            'selling_price' => 15000,
            'current_stock' => 10,
            'active' => true,
            'outlet_id' => $outlet->id,
        ]);

        return compact('outlet', 'user', 'pm', 'product');
    }

    public function test_checkout_uses_server_price_not_client_price(): void
    {
        ['outlet' => $outlet, 'user' => $user, 'pm' => $pm, 'product' => $product] = $this->makeContext();

        $svc = app(CheckoutService::class);
        $order = $svc->checkout([
            'outlet_id' => $outlet->id,
            'items' => [[
                'product_id' => $product->id,
                'product_variant_id' => null,
                'quantity' => 2,
                'discount_percent' => 0,
                // attacker mencoba kirim harga Rp 1 — harus diabaikan
            ]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 30000]],
            'use_tax' => false,
        ], $user->id);

        $this->assertEquals(30000, (float) $order->total_amount);
        $this->assertEquals(15000, (float) $order->orderItems->first()->unit_price);
        $this->assertEquals(8, (int) $product->fresh()->current_stock);
    }

    public function test_checkout_rejects_insufficient_stock(): void
    {
        ['outlet' => $outlet, 'user' => $user, 'pm' => $pm, 'product' => $product] = $this->makeContext();

        $this->expectException(ValidationException::class);
        app(CheckoutService::class)->checkout([
            'outlet_id' => $outlet->id,
            'items' => [['product_id' => $product->id, 'quantity' => 999]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 1]],
            'use_tax' => false,
        ], $user->id);
    }

    public function test_order_numbers_are_unique(): void
    {
        ['outlet' => $outlet, 'user' => $user, 'pm' => $pm, 'product' => $product] = $this->makeContext();
        $svc = app(CheckoutService::class);

        $numbers = [];
        for ($i = 0; $i < 3; $i++) {
            $o = $svc->checkout([
                'outlet_id' => $outlet->id,
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'payments' => [['payment_method_id' => $pm->id, 'amount' => 15000]],
                'use_tax' => false,
            ], $user->id);
            $numbers[] = $o->order_number;
        }

        $this->assertCount(3, array_unique($numbers));
        $this->assertEquals(3, Order::count());
    }
}
