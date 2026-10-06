<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function ctx(): array
    {
        $outlet = Outlet::create(['name' => 'Toko', 'code' => 'T1', 'active' => true]);
        $owner = User::factory()->create(['role' => 'owner']);
        $owner->outlets()->attach($outlet->id);
        $pm = PaymentMethod::create(['name' => 'Tunai', 'code' => 'CASH', 'active' => true]);
        $p = Product::create([
            'name' => 'Indomie', 'slug' => 'indomie-'.uniqid(), 'sku' => 'SKU'.strtoupper(substr(uniqid(), -6)),
            'barcode' => '899'.random_int(1000000000, 9999999999),
            'cost_price' => 3000, 'selling_price' => 5000, 'current_stock' => 100,
            'active' => true, 'outlet_id' => $outlet->id,
        ]);

        return compact('outlet', 'owner', 'pm', 'p');
    }

    protected function makeOrder(array $c, string $date): Order
    {
        $order = app(CheckoutService::class)->checkout([
            'outlet_id' => $c['outlet']->id,
            'items' => [['product_id' => $c['p']->id, 'quantity' => 1]],
            'payments' => [['payment_method_id' => $c['pm']->id, 'amount' => 5000]],
            'use_tax' => false, 'skip_promo' => true,
        ], $c['owner']->id);
        $order->created_at = now()->parse($date);
        $order->save();

        return $order;
    }

    public function test_history_filters_by_date_range(): void
    {
        $c = $this->ctx();
        $this->makeOrder($c, now()->format('Y-m-d'));
        $this->makeOrder($c, now()->subDays(10)->format('Y-m-d'));

        $token = $c['owner']->createToken('t', ['pos-access'])->plainTextToken;
        $res = $this->withHeader('Authorization', "Bearer $token")->getJson(
            '/api/v1/orders/history?start_date='.now()->subDays(6)->format('Y-m-d').'&end_date='.now()->format('Y-m-d')
        );
        $res->assertOk();
        $this->assertCount(1, $res->json('data'));
    }

    public function test_history_rejects_range_over_93_days(): void
    {
        $c = $this->ctx();
        $token = $c['owner']->createToken('t', ['pos-access'])->plainTextToken;
        $res = $this->withHeader('Authorization', "Bearer $token")->getJson(
            '/api/v1/orders/history?start_date='.now()->subDays(100)->format('Y-m-d').'&end_date='.now()->format('Y-m-d')
        );
        $res->assertStatus(422);
    }

    public function test_history_requires_auth(): void
    {
        $this->getJson('/api/v1/orders/history')->assertUnauthorized();
    }
}
