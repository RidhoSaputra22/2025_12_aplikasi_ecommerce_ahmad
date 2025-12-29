<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\VendorBankAccount>
 */
class VendorBankAccountFactory extends Factory
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
            'bank_name' => fake()->randomElement(['BCA', 'BRI', 'BNI', 'Mandiri', 'CIMB']),
            'account_number' => fake()->unique()->numerify('############'),
            'account_holder' => fake()->name(),
            'is_active' => fake()->boolean(90),
        ];
    }
}
