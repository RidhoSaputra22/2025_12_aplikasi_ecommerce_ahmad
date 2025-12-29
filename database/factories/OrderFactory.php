<?php

namespace Database\Factories;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\Order>
 */
class OrderFactory extends Factory
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
            'order_number' => 'ORD-' . strtoupper(fake()->unique()->bothify('##########')),
            'total_amount' => fake()->randomFloat(2, 10000, 50000000),
            'status' => fake()->randomElement(OrderStatus::cases()),
            'payment_status' => fake()->randomElement(OrderPaymentStatus::cases()),
        ];
    }
}
