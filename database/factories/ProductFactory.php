<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->slug(),
            'sku' => 'SKU-' . fake()->unique()->numerify('#####'),
            'barcode' => fake()->unique()->ean13(),
            'cost_price' => fake()->numberBetween(5000, 50000),
            'selling_price' => fake()->numberBetween(10000, 100000),
            'wholesale_price' => fake()->numberBetween(8000, 80000),
            'member_price' => fake()->numberBetween(9000, 90000),
            'current_stock' => fake()->numberBetween(0, 200),
            'min_stock' => fake()->numberBetween(5, 20),
            'max_stock' => fake()->numberBetween(50, 500),
            'active' => true,
        ];
    }
}
