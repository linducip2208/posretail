<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosFinalTest extends TestCase
{
    use RefreshDatabase;

    protected function ctx(): array
    {
        $outlet = Outlet::create(['name' => 'Toko', 'code' => 'T1', 'active' => true]);
        $owner = User::factory()->create(['role' => 'owner', 'commission_percent' => 5]);
        $kasir = User::factory()->create(['role' => 'kasir']);
        foreach ([$owner, $kasir] as $u) {
            $u->outlets()->attach($outlet->id);
        }
        $pm = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
        $p = Product::create([
            'name' => 'Indomie', 'slug' => 'indomie-'.uniqid(), 'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'barcode' => '899'.random_int(1000000000, 9999999999),
            'cost_price' => 3000, 'selling_price' => 5000, 'current_stock' => 100,
            'active' => true, 'outlet_id' => $outlet->id,
        ]);

        return compact('outlet', 'owner', 'kasir', 'pm', 'p');
    }

    public function test_format_order_hides_commission(): void
    {
        ['outlet' => $o, 'owner' => $u, 'pm' => $pm, 'p' => $p] = $this->ctx();
        $order = app(CheckoutService::class)->checkout([
            'outlet_id' => $o->id,
            'items' => [['product_id' => $p->id, 'quantity' => 2]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 10000]],
            'use_tax' => false, 'skip_promo' => true,
        ], $u->id);

        $token = $u->createToken('t', ['pos-access', 'owner'])->plainTextToken;
        $res = $this->withHeader('Authorization', "Bearer $token")->getJson("/api/v1/orders/{$order->id}");
        $res->assertOk();
        $this->assertArrayNotHasKey('commission_amount', $res->json('data'));
        $this->assertArrayHasKey('items', $res->json('data'));
    }

    public function test_old_role_token_still_works_but_unknown_ability_rejected(): void
    {
        ['outlet' => $o, 'kasir' => $k, 'pm' => $pm, 'p' => $p] = $this->ctx();

        $oldToken = $k->createToken('old', ['kasir'])->plainTextToken;
        $res = $this->withHeader('Authorization', "Bearer $oldToken")->postJson('/api/v1/orders', [
            'outlet_id' => $o->id,
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 5000]],
        ]);
        $res->assertCreated();

        $badToken = $k->createToken('bad', ['other-ability'])->plainTextToken;
        // Guard Sanctum di-cache per application instance — lupakan agar request
        // kedua benar-benar otentikasi ulang dengan token baru (seperti request HTTP asli).
        $this->app['auth']->forgetGuards();
        $res2 = $this->withHeader('Authorization', "Bearer $badToken")->postJson('/api/v1/orders', [
            'outlet_id' => $o->id,
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 5000]],
        ]);
        $res2->assertForbidden();
    }

    public function test_product_pagination_capped(): void
    {
        ['kasir' => $k] = $this->ctx();
        $token = $k->createToken('t', ['pos-access'])->plainTextToken;
        $res = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/v1/products?per_page=9999');
        $res->assertOk();
        $this->assertLessThanOrEqual(100, count($res->json('data')));
    }

    public function test_receipt_sizes_and_reprint(): void
    {
        ['outlet' => $o, 'owner' => $u, 'pm' => $pm, 'p' => $p] = $this->ctx();
        $order = app(CheckoutService::class)->checkout([
            'outlet_id' => $o->id,
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $pm->id, 'amount' => 5000]],
            'use_tax' => false, 'skip_promo' => true,
        ], $u->id);

        $this->actingAs($u)->get("/admin/orders/{$order->id}/receipt?size=58")->assertOk();
        $this->actingAs($u)->get("/admin/orders/{$order->id}/receipt?size=80&reprint=1")
            ->assertOk()->assertSee('CETAK ULANG');
    }

    public function test_cloud_backup_skips_gracefully_without_s3(): void
    {
        config()->set('filesystems.disks.s3.key', null);
        config()->set('filesystems.disks.s3.bucket', null);
        $this->artisan('pos:cloud-backup')->assertSuccessful();
    }
}
