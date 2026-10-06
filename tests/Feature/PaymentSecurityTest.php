<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function ctx(): array
    {
        $a = Outlet::create(['name' => 'A', 'code' => 'A1', 'active' => true]);
        $b = Outlet::create(['name' => 'B', 'code' => 'B1', 'active' => true]);
        $kasir = User::factory()->create(['role' => 'kasir']);
        $kasir->outlets()->attach($a->id);
        $cash = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'type' => 'offline', 'active' => true]);
        $gw = Provider::create([
            'name' => 'GW', 'type' => 'payment', 'api_format' => 'rest-redirect',
            'base_url' => 'https://gateway.example.invalid', 'is_active' => true,
        ]);
        $qris = PaymentMethod::create([
            'name' => 'QRIS', 'code' => 'QRIS', 'type' => 'online',
            'active' => true, 'provider_id' => $gw->id, 'is_gateway' => true,
        ]);
        $p = Product::create([
            'name' => 'Indomie', 'slug' => 'indomie-'.uniqid(), 'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'barcode' => '899'.random_int(1000000000, 9999999999),
            'cost_price' => 3000, 'selling_price' => 5000, 'current_stock' => 100,
            'active' => true, 'outlet_id' => $a->id,
        ]);
        $pb = Product::create([
            'name' => 'Mie B', 'slug' => 'mieb-'.uniqid(), 'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'barcode' => '899'.random_int(1000000000, 9999999999),
            'cost_price' => 3000, 'selling_price' => 5000, 'current_stock' => 100,
            'active' => true, 'outlet_id' => $b->id,
        ]);

        return compact('a', 'b', 'kasir', 'cash', 'gw', 'qris', 'p', 'pb');
    }

    protected function order(array $c, int $outletId): Order
    {
        $productId = $outletId === $c['b']->id ? $c['pb']->id : $c['p']->id;

        return app(CheckoutService::class)->checkout([
            'outlet_id' => $outletId,
            'items' => [['product_id' => $productId, 'quantity' => 2]],
            'payments' => [],
            'use_tax' => false, 'skip_promo' => true,
        ], $c['kasir']->id);
    }

    public function test_gateway_amount_must_match_remaining(): void
    {
        $c = $this->ctx();
        $order = $this->order($c, $c['a']->id); // total 10000, belum bayar
        $token = $c['kasir']->createToken('t', ['pos-access'])->plainTextToken;
        $h = ['Authorization' => "Bearer $token"];

        // Nominal ngawur (1000 dari 10000) → 422, tanpa HTTP ke gateway.
        $this->withHeaders($h)->postJson('/api/v1/payment/create', [
            'payment_method_id' => $c['qris']->id,
            'order_number' => $order->order_number,
            'amount' => 1000,
        ])->assertStatus(422);

        // Order outlet lain → 403.
        $this->app['auth']->forgetGuards();
        $ob = $this->order($c, $c['b']->id);
        $this->withHeaders($h)->postJson('/api/v1/payment/create', [
            'payment_method_id' => $c['qris']->id,
            'order_number' => $ob->order_number,
            'amount' => 10000,
        ])->assertForbidden();
    }

    public function test_webhook_replay_cannot_downgrade_success(): void
    {
        config()->set('license.dev_bypass', true);
        $c = $this->ctx();
        $order = $this->order($c, $c['a']->id);
        $payment = Payment::create([
            'order_id' => $order->id, 'payment_method_id' => $c['qris']->id,
            'amount' => 10000, 'status' => 'pending',
        ]);

        // Webhook success → paid.
        $r1 = $this->postJson('/api/v1/webhooks/QRIS', [
            'order_id' => $order->order_number, 'status' => 'success',
        ]);
        $r1->assertOk();
        $this->assertSame('success', $payment->fresh()->status);

        // Replay webhook failure SETELAH success → diabaikan, tetap success.
        $r2 = $this->postJson('/api/v1/webhooks/QRIS', [
            'order_id' => $order->order_number, 'status' => 'failure',
        ]);
        $r2->assertOk();
        $this->assertSame('success', $payment->fresh()->status);
        $this->assertSame('replay', $r2->json('ignored'));
    }
}
