<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosCsrfRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_csrf_endpoint_requires_auth(): void
    {
        // Klien JS (fetch JSON) yang sesinya habis mendapat 401 → diarahkan login oleh klien.
        $this->getJson('/pos/csrf')->assertUnauthorized();
    }

    public function test_csrf_endpoint_returns_session_token(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);
        $token = $this->actingAs($user)->getJson('/pos/csrf')->assertOk()->json('token');
        $this->assertNotEmpty($token);
        $this->assertSame(session()->token(), $token);
    }

    public function test_checkout_succeeds_with_fresh_token_payload(): void
    {
        $outlet = Outlet::create(['name' => 'Toko', 'code' => 'T1', 'active' => true]);
        $kasir = User::factory()->create(['role' => 'kasir']);
        $kasir->outlets()->attach($outlet->id);
        $pm = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
        $p = Product::create([
            'name' => 'Indomie', 'slug' => 'indomie-'.uniqid(), 'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'barcode' => '899'.random_int(1000000000, 9999999999),
            'cost_price' => 3000, 'selling_price' => 5000, 'current_stock' => 100,
            'active' => true, 'outlet_id' => $outlet->id,
        ]);

        // Catatan: middleware CSRF Laravel menonaktifkan verifikasi saat unit test
        // (runningUnitTests), jadi 419 asli hanya terjadi di browser/HP. Test ini
        // memastikan payload + endpoint recovery valid; retry-419 diuji di sisi klien.
        $fresh = $this->actingAs($kasir)->getJson('/pos/csrf')->json('token');
        $this->actingAs($kasir)->postJson('/pos/checkout', [
            'outlet_id' => $outlet->id,
            'items' => [['id' => $p->id, 'qty' => 1, 'price' => 5000]],
            'payment_method_id' => $pm->id,
            'paid_amount' => 5000,
        ], ['X-CSRF-TOKEN' => $fresh])->assertOk()->assertJsonPath('success', true);
    }

    public function test_client_has_419_recovery_logic(): void
    {
        $pos = file_get_contents(resource_path('views/pos/index.blade.php'));
        $this->assertStringContainsString('/pos/csrf', $pos);
        $this->assertStringContainsString('419', $pos);

        $offline = file_get_contents(public_path('js/pos-offline.js'));
        $this->assertStringContainsString('/pos/csrf', $offline);
        $this->assertStringContainsString('419', $offline);
    }
}
