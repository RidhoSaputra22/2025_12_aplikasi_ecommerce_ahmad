<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\VendorAddress>
 */
class VendorAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'province' => fake()->state(),
            'city' => fake()->city(),
            'district' => fake()->citySuffix(),
            'postal_code' => fake()->postcode(),
            'address' => fake()->address(),
            'is_primary' => fake()->boolean(20),
        ];
    }
}
