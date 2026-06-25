<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_user(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'user']);
        $response->assertJsonPath('user.email', $user->email);
        $response->assertJsonPath('user.role', 'kasir');
    }

    public function test_authenticated_products_endpoint_returns_data_array(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);

        Product::factory()->create(['active' => true, 'name' => 'Test Product']);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/products');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'meta']);
        $response->assertJsonCount(1, 'data');
    }

    public function test_authenticated_payment_methods_returns_data(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);

        PaymentMethod::factory()->create(['active' => true, 'name' => 'Cash']);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/payment-methods');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    public function test_tables_endpoint_returns_data(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/tables');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    public function test_user_endpoint_returns_logged_in_user_info(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/user');

        $response->assertStatus(200);
        $response->assertJsonPath('email', $user->email);
        $response->assertJsonPath('role', 'kasir');
    }

    public function test_create_order_with_valid_payload(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);
        $outlet = Outlet::factory()->create(['active' => true]);
        $product = Product::factory()->create([
            'active' => true,
            'selling_price' => 15000,
            'current_stock' => 100,
            'outlet_id' => $outlet->id,
        ]);
        $paymentMethod = PaymentMethod::factory()->create(['active' => true]);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'unit_price' => 15000,
                        'discount_percent' => 0,
                    ],
                ],
                'payments' => [
                    [
                        'payment_method_id' => $paymentMethod->id,
                        'amount' => 30000,
                    ],
                ],
                'outlet_id' => $outlet->id,
                'order_type' => 'dine_in',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data']);
        $response->assertJsonPath('data.total_amount', 30000);
        $response->assertJsonPath('data.order_status', 'completed');
    }

    public function test_orders_today_returns_list(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);
        $outlet = Outlet::factory()->create(['active' => true]);
        $product = Product::factory()->create([
            'active' => true,
            'selling_price' => 10000,
            'current_stock' => 50,
            'outlet_id' => $outlet->id,
        ]);
        $paymentMethod = PaymentMethod::factory()->create(['active' => true]);

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'unit_price' => 10000,
                    ],
                ],
                'payments' => [
                    [
                        'payment_method_id' => $paymentMethod->id,
                        'amount' => 10000,
                    ],
                ],
                'outlet_id' => $outlet->id,
            ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/orders/today?outlet_id=' . $outlet->id);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $response->assertJsonCount(1, 'data');
    }

    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/v1/products');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/tables');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/user');
        $response->assertStatus(401);
    }
}
