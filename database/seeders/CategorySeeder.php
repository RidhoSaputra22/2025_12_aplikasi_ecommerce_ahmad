<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Category::factory()
            ->has(
                Product::factory()
                ->count(10)
                ->has(
                    ProductImage::factory()
                    ->count(1)
                )
                ->has(
                    ProductVariant::factory()->count(1)
                )
                ->has(
                    Review::factory()->count(5)
                )

            )
            ->create([
                'name' => 'Kerajinan Lokal',
            ]);
        Category::factory()
            ->has(
                Product::factory()
                ->count(10)
                ->has(
                    ProductImage::factory()
                    ->count(1)
                )
                ->has(
                    ProductVariant::factory()->count(1)
                )

            )
            ->create([
                'name' => 'Sayur dan Buah',
            ]);
        Category::factory()
            ->has(
                Product::factory()
                ->count(10)
                ->has(
                    ProductImage::factory()
                    ->count(1)
                )
                ->has(
                    ProductVariant::factory()->count(1)
                )

            )
            ->create([
                'name' => 'Makanan Ringan',
            ]);
        Category::factory()
            ->has(
                Product::factory()
                ->count(10)
                ->has(
                    ProductImage::factory()
                    ->count(1)
                )
                ->has(
                    ProductVariant::factory()->count(1)
                )

            )
            ->create([
                'name' => 'Pakaian dan Aksesoris',
            ]);
        Category::factory()
            ->has(
                Product::factory()
                ->count(10)
                ->has(
                    ProductImage::factory()
                    ->count(1)
                )
                ->has(
                    ProductVariant::factory()->count(1)
                )

            )
            ->create([
                'name' => 'Produk Digital',
            ]);
    }
}
