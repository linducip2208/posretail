<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression test untuk hardening keamanan:
 * - kredensial demo tidak bocor di production
 * - seeder demo tidak jalan di production
 * - endpoint AJAX POS wajib login + customer display ter-scope outlet
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Kredensial demo tidak boleh tampil di luar instance demo / local
    // ------------------------------------------------------------------

    public function test_marketing_and_docs_hide_demo_credentials_in_production(): void
    {
        $this->app['env'] = 'production';
        config(['pos.demo_mode' => false]);

        $this->get('/')->assertOk()->assertDontSee('owner@pos-retail.test');
        $this->get('/docs')->assertOk()->assertDontSee('owner@pos-retail.test');
    }

    public function test_login_view_hides_demo_block_when_demo_mode_off(): void
    {
        $this->app['env'] = 'production';
        config(['pos.demo_mode' => false]);

        $html = $this->app['view']
            ->make('auth.login', ['errors' => new \Illuminate\Support\ViewErrorBag()])
            ->render();

        $this->assertStringNotContainsString('owner@pos-retail.test', $html);
    }

    public function test_docs_show_demo_credentials_when_demo_mode_enabled(): void
    {
        config(['pos.demo_mode' => true]);

        $this->get('/docs')->assertOk()->assertSee('owner@pos-retail.test');
    }

    // ------------------------------------------------------------------
    // Seeder demo tidak boleh jalan di production tanpa izin eksplisit
    // ------------------------------------------------------------------

    public function test_demo_seeder_is_skipped_in_production(): void
    {
        $this->app['env'] = 'production';
        config(['pos.allow_demo_seed' => false]);

        (new DemoDataSeeder())->run();

        $this->assertDatabaseMissing('users', ['email' => 'owner@pos-retail.test']);
    }

    public function test_disable_demo_accounts_command(): void
    {
        $user = User::factory()->create(['email' => 'owner@pos-retail.test', 'active' => true]);

        $this->artisan('pos:disable-demo-accounts', ['--force' => true])->assertSuccessful();

        $this->assertFalse((bool) $user->fresh()->active);
    }

    // ------------------------------------------------------------------
    // Endpoint AJAX POS wajib login + display ter-scope outlet
    // ------------------------------------------------------------------

    public function test_pos_api_endpoints_require_authentication(): void
    {
        $this->getJson('/api/pos/products')->assertStatus(401);
        $this->getJson('/api/pos/barcode/899123')->assertStatus(401);
        $this->getJson('/api/pos/display')->assertStatus(401);
        // Route /pos/* dirender sebagai JSON 401 untuk request tanpa sesi (by design).
        $this->get('/pos/display')->assertStatus(401);
    }

    public function test_pos_products_accessible_for_authenticated_user(): void
    {
        $outlet = Outlet::create(['name' => 'A', 'code' => 'A1', 'active' => true]);
        $user = User::factory()->create(['role' => 'kasir']);
        $user->outlets()->attach($outlet->id);
        Product::create([
            'name' => 'Indomie', 'slug' => 'indomie-'.uniqid(), 'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'barcode' => '899'.random_int(1000000000, 9999999999),
            'cost_price' => 3000, 'selling_price' => 5000, 'current_stock' => 100,
            'active' => true, 'outlet_id' => $outlet->id,
        ]);

        $res = $this->actingAs($user)->getJson('/api/pos/products');

        $res->assertOk()->assertJsonPath('data.0.name', 'Indomie');
    }

    public function test_customer_display_is_scoped_to_accessible_outlets(): void
    {
        $a = Outlet::create(['name' => 'A', 'code' => 'A1', 'active' => true]);
        $b = Outlet::create(['name' => 'B', 'code' => 'B1', 'active' => true]);
        $kasir = User::factory()->create(['role' => 'kasir']);
        $kasir->outlets()->attach($a->id);
        $pm = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
        $product = Product::create([
            'name' => 'Indomie', 'slug' => 'indomie-'.uniqid(), 'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'barcode' => '899'.random_int(1000000000, 9999999999),
            'cost_price' => 3000, 'selling_price' => 5000, 'current_stock' => 100,
            'active' => true, 'outlet_id' => $a->id,
        ]);

        $order = app(CheckoutService::class)->checkout([
            'outlet_id' => $a->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 5000]],
        ], $kasir->id);

        // Outlet milik kasir → tampil
        $this->actingAs($kasir)->getJson('/api/pos/display?outlet_id='.$a->id)
            ->assertOk()->assertJsonPath('order_id', $order->id);

        // Outlet asing → tidak boleh bocor
        $res = $this->actingAs($kasir)->getJson('/api/pos/display?outlet_id='.$b->id);
        $res->assertOk();
        $this->assertNull($res->json('order_id'));
    }
}
