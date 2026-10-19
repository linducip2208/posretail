<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Scopes\OutletScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression test idempotensi checkout API — kunci keamanan untuk:
 * - retry setelah timeout (Flutter mengulang POST dengan client_uuid sama)
 * - sinkronisasi antrean offline (sync-batch & POST /orders tidak ganda)
 */
class OrderIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private function makeOutlet(string $name, string $code): Outlet
    {
        return Outlet::create(['name' => $name, 'code' => $code, 'active' => true]);
    }

    private function makeProduct(Outlet $outlet, int $stock = 50): Product
    {
        return Product::create([
            'name' => 'Kopi Susu',
            'slug' => 'kopi-'.uniqid(),
            'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'barcode' => '899'.random_int(1000000000, 9999999999),
            'cost_price' => 3000,
            'selling_price' => 5000,
            'current_stock' => $stock,
            'active' => true,
            'outlet_id' => $outlet->id,
        ]);
    }

    public function test_post_orders_returns_existing_order_for_same_client_uuid(): void
    {
        $outlet = $this->makeOutlet('A', 'A1');
        $user = User::factory()->create(['role' => 'kasir']);
        $user->outlets()->attach($outlet->id);
        $pm = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
        $product = $this->makeProduct($outlet);

        $uuid = 'test-uuid-'.uniqid();
        $payload = [
            'client_uuid' => $uuid,
            'outlet_id' => $outlet->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 5000]],
        ];

        $first = $this->actingAs($user)->postJson('/api/v1/orders', $payload);
        $first->assertCreated();
        $orderId = $first->json('data.id');

        // Retry dengan uuid sama → sukses, kembalikan order yang sama.
        $second = $this->actingAs($user)->postJson('/api/v1/orders', $payload);
        $second->assertOk()
            ->assertJsonPath('duplicate', true)
            ->assertJsonPath('data.id', $orderId);

        // Tidak ada order ganda & stok tidak terpotong dua kali.
        $this->assertSame(1, Order::withoutGlobalScope(OutletScope::class)->where('client_uuid', $uuid)->count());
        $this->assertSame(49, (int) $product->fresh()->current_stock);
    }

    public function test_orders_without_client_uuid_stay_supported(): void
    {
        $outlet = $this->makeOutlet('A', 'A1');
        $user = User::factory()->create(['role' => 'kasir']);
        $user->outlets()->attach($outlet->id);
        $pm = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
        $product = $this->makeProduct($outlet);

        $this->actingAs($user)->postJson('/api/v1/orders', [
            'outlet_id' => $outlet->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 5000]],
        ])->assertCreated();
    }

    public function test_replaying_foreign_client_uuid_does_not_leak_order(): void
    {
        $outletA = $this->makeOutlet('A', 'A1');
        $outletB = $this->makeOutlet('B', 'B1');
        $userA = User::factory()->create(['role' => 'kasir']);
        $userA->outlets()->attach($outletA->id);
        $userB = User::factory()->create(['role' => 'kasir']);
        $userB->outlets()->attach($outletB->id);
        $pm = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
        $productA = $this->makeProduct($outletA);
        $productB = $this->makeProduct($outletB);

        $uuid = 'test-uuid-'.uniqid();

        $this->actingAs($userA)->postJson('/api/v1/orders', [
            'client_uuid' => $uuid,
            'outlet_id' => $outletA->id,
            'items' => [['product_id' => $productA->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 5000]],
        ])->assertCreated();

        // Kasir outlet lain menebak uuid → tidak boleh melihat order tsb.
        $res = $this->actingAs($userB)->postJson('/api/v1/orders', [
            'client_uuid' => $uuid,
            'outlet_id' => $outletB->id,
            'items' => [['product_id' => $productB->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 5000]],
        ]);

        $res->assertStatus(422);
        $this->assertNull($res->json('data'));
        $this->assertSame(1, Order::withoutGlobalScope(OutletScope::class)->where('client_uuid', $uuid)->count());
    }
}
