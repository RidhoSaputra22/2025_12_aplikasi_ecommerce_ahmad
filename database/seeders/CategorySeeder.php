<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
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
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName]
            );
        }
    }
}
