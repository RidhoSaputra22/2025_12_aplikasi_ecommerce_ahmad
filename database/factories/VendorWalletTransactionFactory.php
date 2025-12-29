<?php

namespace Database\Factories;

use App\Enums\VendorWalletTransactionType;
use App\Models\VendorWallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\VendorWalletTransaction>
 */
class VendorWalletTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_wallet_id' => VendorWallet::factory(),
            'type' => fake()->randomElement(VendorWalletTransactionType::cases()),
            'amount' => fake()->randomFloat(2, 1000, 5000000),
            'description' => fake()->boolean(70) ? fake()->sentence(rand(6, 12)) : null,
            'reference_id' => fake()->boolean(70) ? fake()->unique()->bothify('VW-##########') : null,
        ];
    }
}
