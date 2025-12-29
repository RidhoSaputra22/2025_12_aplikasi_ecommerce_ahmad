<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\ShipmentCourier>
 */
class ShipmentCourierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['JNE', 'J&T', 'SiCepat', 'POS', 'AnterAja']),
            'code' => fake()->unique()->bothify('CRR-####'),
            'service' => fake()->randomElement(['REG', 'YES', 'ECO', 'SAMEDAY']),
            'price' => fake()->randomFloat(2, 5000, 80000),
        ];
    }
}
