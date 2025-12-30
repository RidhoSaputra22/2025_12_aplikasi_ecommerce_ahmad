<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\Category;
use App\Enums\ProductStatus;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->sentence($nbWords = 3, $variableNbWords = true);

        return [
            'vendor_id' => Vendor::inRandomOrder()->first()?->id ?? Vendor::factory(),
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numerify('####'),
            'description' => fake()->paragraphs(rand(2, 4), true),
            'price' => fake()->numberBetween(10000, 10000000),
            'weight' => fake()->numberBetween(100, 5000),
            'status' => fake()->randomElement(ProductStatus::cases()),
        ];
    }
}
