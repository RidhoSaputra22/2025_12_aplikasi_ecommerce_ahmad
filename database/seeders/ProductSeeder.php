<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Kerajinan Lokal',
            'Sayur dan Buah',
            'Makanan Ringan',
            'Pakaian dan Aksesoris',
            'Produk Digital',
        ];

        foreach ($categories as $categoryName) {
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
                    'name' => $categoryName,
                ]);

        }
    }
}
