<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(PaymentStatus::cases());

        return [
            'order_id' => Order::factory(),
            'payment_method' => fake()->randomElement(['bank_transfer', 'qris', 'ewallet', 'cod']),
            'payment_gateway' => fake()->randomElement([null, 'midtrans', 'xendit', 'manual']),
            'amount' => fake()->randomFloat(2, 10000, 50000000),
            'status' => $status,
            'transaction_reference' => fake()->boolean(70) ? fake()->unique()->bothify('TRX-##########') : null,
            'paid_at' => $status === PaymentStatus::Success ? fake()->dateTimeBetween('-14 days', 'now') : null,
        ];
    }
}
