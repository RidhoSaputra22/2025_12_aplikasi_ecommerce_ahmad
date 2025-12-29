<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat 50 produk menggunakan factory
        Product::factory()
            ->count(50)
            ->hasProductImages(3)
            ->hasProductVariants(2)
            ->create();
    }
}
