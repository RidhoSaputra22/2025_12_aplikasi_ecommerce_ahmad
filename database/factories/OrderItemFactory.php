<?php

namespace Database\Factories;

use App\Models\OrderVendor;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 10000, 10000000);
        $quantity = fake()->numberBetween(1, 5);

        return [
            'order_vendor_id' => OrderVendor::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'price' => $price,
            'quantity' => $quantity,
            'total' => $price * $quantity,
        ];
    }
}
