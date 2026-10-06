<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function ctx(): array
    {
        $a = Outlet::create(['name' => 'A', 'code' => 'A1', 'active' => true]);
        $b = Outlet::create(['name' => 'B', 'code' => 'B1', 'active' => true]);
        $kasir = User::factory()->create(['role' => 'kasir']);
        $kasir->outlets()->attach($a->id);
        $pm = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
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

        return compact('a', 'b', 'kasir', 'pm', 'p', 'pb');
    }

    public function test_sync_batch_idempotent_on_retry(): void
    {
        ['a' => $a, 'kasir' => $k, 'pm' => $pm, 'p' => $p] = $this->ctx();
        $token = $k->createToken('t', ['pos-access'])->plainTextToken;
        $payload = ['orders' => [[
            'client_uuid' => 'uuid-test-123',
            'outlet_id' => $a->id,
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 5000]],
        ]]];
        $h = ['Authorization' => "Bearer $token"];

        $r1 = $this->withHeaders($h)->postJson('/api/v1/orders/sync-batch', $payload);
        $r1->assertOk();
        $this->assertSame('created', $r1->json('data.0.status'));

        $this->app['auth']->forgetGuards();
        $r2 = $this->withHeaders($h)->postJson('/api/v1/orders/sync-batch', $payload);
        $r2->assertOk();
        $this->assertSame('duplicate', $r2->json('data.0.status'));
        $this->assertSame($r1->json('data.0.id'), $r2->json('data.0.id'));
    }

    public function test_sync_batch_rejects_foreign_outlet(): void
    {
        ['b' => $b, 'kasir' => $k, 'pm' => $pm, 'p' => $p] = $this->ctx();
        $token = $k->createToken('t', ['pos-access'])->plainTextToken;
        $res = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/v1/orders/sync-batch', ['orders' => [[
            'client_uuid' => 'uuid-foreign-1',
            'outlet_id' => $b->id,
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 5000]],
        ]]]);
        $res->assertOk();
        $this->assertSame('failed', $res->json('data.0.status'));
    }

    public function test_checkout_rejects_product_from_other_outlet(): void
    {
        ['a' => $a, 'kasir' => $k, 'pm' => $pm, 'pb' => $pb] = $this->ctx();
        $token = $k->createToken('t', ['pos-access'])->plainTextToken;
        $res = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/v1/orders', [
            'outlet_id' => $a->id,
            'items' => [['product_id' => $pb->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 5000]],
        ]);
        $res->assertStatus(422);
    }

    public function test_shift_open_close_foreign_outlet_forbidden(): void
    {
        ['b' => $b, 'kasir' => $k] = $this->ctx();
        $token = $k->createToken('t', ['pos-access'])->plainTextToken;
        $h = ['Authorization' => "Bearer $token"];

        $this->withHeaders($h)->postJson('/api/v1/shifts/open', [
            'outlet_id' => $b->id, 'starting_cash' => 100000,
        ])->assertForbidden();

        $shift = Shift::create([
            'outlet_id' => $b->id, 'user_id' => $k->id,
            'started_at' => now(), 'starting_cash' => 100000, 'status' => 'open',
        ]);
        $this->app['auth']->forgetGuards();
        // Global outlet scope menyembunyikan shift outlet lain → 404 (tetap aman).
        $this->withHeaders($h)->postJson("/api/v1/shifts/{$shift->id}/close", [
            'ending_cash' => 100000,
        ])->assertNotFound();
    }

    public function test_deposit_foreign_outlet_forbidden(): void
    {
        ['b' => $b, 'kasir' => $k] = $this->ctx();
        $customer = Customer::create(['name' => 'C', 'phone' => '081']);
        $token = $k->createToken('t', ['pos-access'])->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")->postJson(
            "/api/v1/customers/{$customer->id}/deposits",
            ['outlet_id' => $b->id, 'type' => 'topup', 'amount' => 50000]
        )->assertForbidden();
    }

    public function test_marketplace_webhook_token_enforced_when_set(): void
    {
        config()->set('services.marketplace.webhook_token', 's3cr3t');
        $this->postJson('/api/v1/webhooks/marketplace/tokopedia', ['order_id' => 'X1'])
            ->assertUnauthorized();
        $this->withHeader('X-Webhook-Token', 's3cr3t')
            ->postJson('/api/v1/webhooks/marketplace/tokopedia', ['order_id' => 'X1'])
            ->assertCreated();
    }

    public function test_login_and_user_include_accessible_outlets(): void
    {
        ['a' => $a, 'kasir' => $k] = $this->ctx();
        $res = $this->postJson('/api/v1/login', ['email' => $k->email, 'password' => 'password']);
        $res->assertOk();
        $this->assertSame($a->id, $res->json('user.outlets.0.id'));

        $token = $res->json('token');
        $me = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/v1/user');
        $me->assertOk()->assertJsonPath('outlets.0.id', $a->id);

        $this->app['auth']->forgetGuards();
        $outlets = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/v1/outlets');
        $outlets->assertOk();
        $this->assertCount(1, $outlets->json('data'));
    }

    public function test_api_responses_carry_request_id(): void
    {
        ['kasir' => $k] = $this->ctx();
        $token = $k->createToken('t', ['pos-access'])->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/orders/today')
            ->assertOk()
            ->assertHeader('X-Request-ID');
    }
}
