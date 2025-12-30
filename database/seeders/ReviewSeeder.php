<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Review::factory()

            ->has(
                Product::factory()

                    ->has(
                        ProductImage::factory()
                        ->count(1)
                    )
                    ->has(
                        ProductVariant::factory()->count(1)
                    )

                )
            ->create();
    }
}
