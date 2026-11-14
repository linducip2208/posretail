<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosDuplicateProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function seedPos(): array
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

        return [$kasir, $outlet, $pm, $p];
    }

    private function payload($outlet, $pm, $p, ?string $uuid = null): array
    {
        return array_filter([
            'outlet_id' => $outlet->id,
            'items' => [['id' => $p->id, 'qty' => 1, 'price' => 5000]],
            'payment_method_id' => $pm->id,
            'paid_amount' => 5000,
            'client_uuid' => $uuid,
        ], fn ($v) => $v !== null);
    }

    public function test_double_submit_same_uuid_creates_one_order(): void
    {
        [$kasir, $outlet, $pm, $p] = $this->seedPos();
        $uuid = 'web-'.uniqid();

        $first = $this->actingAs($kasir)->postJson('/pos/checkout', $this->payload($outlet, $pm, $p, $uuid));
        $first->assertOk()->assertJsonPath('success', true);

        // Klik kedua / retry timeout dengan uuid sama → order existing, bukan order baru.
        $second = $this->actingAs($kasir)->postJson('/pos/checkout', $this->payload($outlet, $pm, $p, $uuid));
        $second->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('duplicate', true)
            ->assertJsonPath('order_number', $first->json('order_number'));

        $this->assertSame(1, Order::where('client_uuid', $uuid)->count());
        // Stok hanya terpotong sekali.
        $this->assertSame(99, (int) $p->fresh()->current_stock);
    }

    public function test_checkout_without_uuid_stays_supported(): void
    {
        [$kasir, $outlet, $pm, $p] = $this->seedPos();

        $this->actingAs($kasir)->postJson('/pos/checkout', $this->payload($outlet, $pm, $p))
            ->assertOk()->assertJsonPath('success', true);
        $this->assertSame(1, Order::count());
    }

    public function test_pos_client_has_double_submit_guard(): void
    {
        $pos = file_get_contents(resource_path('views/pos/index.blade.php'));
        $this->assertStringContainsString('isProcessing', $pos);
        $this->assertStringContainsString('client_uuid', $pos);
        $this->assertStringContainsString('processPayBtn', $pos);
    }
}
