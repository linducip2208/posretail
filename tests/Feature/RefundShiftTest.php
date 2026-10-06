<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundShiftTest extends TestCase
{
    use RefreshDatabase;

    protected function ctx(): array
    {
        $outlet = Outlet::create(['name' => 'Toko', 'code' => 'T1', 'active' => true]);
        $owner = User::factory()->create(['role' => 'owner']);
        $owner->outlets()->attach($outlet->id);
        // Hak refund (di production berasal dari RolePermissionSeeder).
        $perm = \App\Models\Permission::create(['name' => 'Hapus Transaksi', 'slug' => 'hapus-transaksi', 'group' => 'transaksi']);
        $role = \App\Models\Role::create(['name' => 'Owner', 'slug' => 'owner']);
        $role->permissions()->sync([$perm->id]);
        $owner->roles()->sync([$role->id]);
        $cash = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'type' => 'offline', 'active' => true]);
        $qris = PaymentMethod::create(['name' => 'QRIS', 'code' => 'QRIS', 'type' => 'online', 'active' => true]);
        $p = Product::create([
            'name' => 'Indomie', 'slug' => 'indomie-'.uniqid(), 'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'barcode' => '899'.random_int(1000000000, 9999999999),
            'cost_price' => 3000, 'selling_price' => 5000, 'current_stock' => 100,
            'active' => true, 'outlet_id' => $outlet->id,
        ]);

        return compact('outlet', 'owner', 'cash', 'qris', 'p');
    }

    protected function checkout(array $c, int $qty, array $pay): \App\Models\Order
    {
        return app(CheckoutService::class)->checkout([
            'outlet_id' => $c['outlet']->id,
            'items' => [['product_id' => $c['p']->id, 'quantity' => $qty]],
            'payments' => $pay,
            'use_tax' => false, 'skip_promo' => true,
        ], $c['owner']->id);
    }

    protected function auth(array $c)
    {
        $token = $c['owner']->createToken('t', ['pos-access', 'owner'])->plainTextToken;

        return ['Authorization' => "Bearer $token"];
    }

    public function test_cumulative_refund_cannot_exceed_purchased(): void
    {
        $c = $this->ctx();
        $order = $this->checkout($c, 10, [['payment_method_id' => $c['cash']->id, 'amount' => 50000]]);
        $itemId = $order->orderItems()->first()->id;
        $h = $this->auth($c);

        // Refund 6 dari 10 → OK.
        $r1 = $this->withHeaders($h)->postJson("/api/v1/orders/{$order->id}/refund", [
            'items' => [['order_item_id' => $itemId, 'quantity' => 6]],
        ]);
        $r1->assertCreated();

        // Refund 5 lagi (total 11 > 10) → HARUS 422.
        $this->app['auth']->forgetGuards();
        $r2 = $this->withHeaders($h)->postJson("/api/v1/orders/{$order->id}/refund", [
            'items' => [['order_item_id' => $itemId, 'quantity' => 5]],
        ]);
        $r2->assertStatus(422);

        // Sisa yang sah: 4 → OK.
        $this->app['auth']->forgetGuards();
        $r3 = $this->withHeaders($h)->postJson("/api/v1/orders/{$order->id}/refund", [
            'items' => [['order_item_id' => $itemId, 'quantity' => 4]],
        ]);
        $r3->assertCreated();
    }

    public function test_shift_cash_excludes_online_payments(): void
    {
        $c = $this->ctx();
        $h = $this->auth($c);

        $open = $this->withHeaders($h)->postJson('/api/v1/shifts/open', [
            'outlet_id' => $c['outlet']->id, 'starting_cash' => 50000,
        ]);
        $open->assertCreated();
        $shiftId = $open->json('data.id');

        // Transaksi SETELAH shift dibuka: tunai 10000 + QRIS 20000.
        $this->checkout($c, 2, [['payment_method_id' => $c['cash']->id, 'amount' => 10000]]);
        $this->checkout($c, 4, [['payment_method_id' => $c['qris']->id, 'amount' => 20000]]);

        $this->app['auth']->forgetGuards();
        $close = $this->withHeaders($h)->postJson("/api/v1/shifts/{$shiftId}/close", [
            'ending_cash' => 60000,
        ]);
        $close->assertOk();

        // Kas harapan = 50000 + 10000 (tunai saja; QRIS 20000 dikecualikan).
        $this->assertEquals(60000, (float) $close->json('data.expected_cash'));
        $this->assertEquals(0, (float) $close->json('data.difference'));
    }
}
