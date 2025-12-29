<?php

namespace Database\Factories;

use App\Enums\VendorStatus;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\\Models\\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $storeName = fake()->unique()->company();

        return [
            'user_id' => User::factory(),
            'store_name' => $storeName,
            'slug' => Str::slug($storeName) . '-' . fake()->unique()->numerify('####'),
            'description' => fake()->boolean(80) ? fake()->paragraph() : null,
            'logo' => null,
            'banner' => null,
            'is_verified' => fake()->boolean(30),
            'rating' => fake()->randomFloat(2, 0, 5),
            'status' => fake()->randomElement(VendorStatus::cases()),
        ];
    }
}
