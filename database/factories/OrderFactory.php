<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'INV-' . fake()->unique()->numerify('########'),
            'customer_id' => Customer::factory(),
            'outlet_id' => Outlet::factory(),
            'user_id' => User::factory(),
            'subtotal' => $sub = fake()->numberBetween(10000, 500000),
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $sub,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ];
    }
}
