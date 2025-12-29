<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\ShipmentAddress>
 */
class ShipmentAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'province' => fake()->state(),
            'city' => fake()->city(),
            'district' => fake()->citySuffix(),
            'postal_code' => fake()->postcode(),
            'address' => fake()->address(),
        ];
    }
}
