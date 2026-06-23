<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Cash', 'Debit', 'QRIS', 'Transfer']),
            'code' => fake()->unique()->lexify('PM???'),
            'type' => fake()->randomElement(['cash', 'bank', 'ewallet', 'qris']),
            'active' => true,
        ];
    }
}
