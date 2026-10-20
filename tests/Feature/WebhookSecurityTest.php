<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_tanpa_token_ditolak_bila_token_dikonfigurasi(): void
    {
        config()->set('services.marketplace.webhook_token', 'rahasia-32-karakter-minimal-abc123');

        $this->postJson('/api/v1/webhooks/marketplace/shopee', ['ordersn' => 'X1'])
            ->assertStatus(401);

        $this->postJson('/api/v1/webhooks/marketplace/shopee', ['ordersn' => 'X1'], ['X-Webhook-Token' => 'salah'])
            ->assertStatus(401);

        $this->postJson('/api/v1/webhooks/marketplace/shopee', ['ordersn' => 'X1'], ['X-Webhook-Token' => 'rahasia-32-karakter-minimal-abc123'])
            ->assertCreated();
        $this->assertDatabaseHas('marketplace_orders', ['platform' => 'shopee', 'platform_order_id' => 'X1']);
    }

    public function test_marketplace_fail_closed_di_production_tanpa_token(): void
    {
        config()->set('services.marketplace.webhook_token', '');
        $this->app['env'] = 'production';

        $this->postJson('/api/v1/webhooks/marketplace/tokopedia', ['order_id' => 'Y1'])
            ->assertStatus(503);
        $this->assertDatabaseMissing('marketplace_orders', ['platform_order_id' => 'Y1']);
    }

    public function test_held_cart_hanya_pemilik_atau_superadmin_yang_hapus(): void
    {
        $owner = \App\Models\User::factory()->create(['role' => 'owner']);
        $kasirA = \App\Models\User::factory()->create(['role' => 'kasir']);
        $kasirB = \App\Models\User::factory()->create(['role' => 'kasir']);
        $outlet = \App\Models\Outlet::create(['name' => 'T', 'code' => 'T01', 'active' => true]);
        foreach ([$owner, $kasirA, $kasirB] as $u) {
            $u->outlets()->attach($outlet->id);
        }

        $cart = \App\Models\HeldCart::create([
            'outlet_id' => $outlet->id, 'user_id' => $kasirA->id, 'items' => [['x' => 1]],
        ]);

        // Kasir lain tidak boleh hapus
        $this->actingAs($kasirB, 'api');
        $this->deleteJson('/api/v1/held-carts/' . $cart->id)->assertForbidden();

        // Pemilik boleh hapus
        $this->actingAs($kasirA, 'api');
        $this->deleteJson('/api/v1/held-carts/' . $cart->id)->assertOk();
        $this->assertDatabaseMissing('held_carts', ['id' => $cart->id]);
    }
}
