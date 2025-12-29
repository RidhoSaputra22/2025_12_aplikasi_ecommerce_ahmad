<?php

namespace Database\Factories;

use App\Enums\OrderVendorStatus;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\OrderVendor>
 */
class OrderVendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'vendor_id' => Vendor::factory(),
            'subtotal' => fake()->randomFloat(2, 10000, 50000000),
            'status' => fake()->randomElement(OrderVendorStatus::cases()),
        ];
    }
}
