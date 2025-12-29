<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => fake()->unique()->bothify('SKU-########'),
            'variant_name' => fake()->randomElement(['Size S', 'Size M', 'Size L', 'Color Red', 'Color Blue', 'Color Black']),
            'price' => fake()->randomFloat(2, 10000, 10000000),
            'stock' => fake()->numberBetween(0, 200),
        ];
    }
}
