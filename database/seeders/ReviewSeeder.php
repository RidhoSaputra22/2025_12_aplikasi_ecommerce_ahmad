<?php

namespace Database\Seeders;

use App\Models\OrderItem;
use App\Models\OrderVendor;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviewer = User::query()->where('email', 'customer@gmail.com')->first()
            ?? User::query()->first()
            ?? User::factory()->create();

        $products = Product::query()
            ->with('productVariants')
            ->get();

        foreach ($products->take(5) as $product) {
            if (Review::query()->where('product_id', $product->id)->where('user_id', $reviewer->id)->exists()) {
                continue;
            }

            $variant = $product->productVariants->first();

            if (! $variant) {
                continue;
            }

            $orderVendor = OrderVendor::factory()->create([
                'vendor_id' => $product->vendor_id,
                'subtotal' => $variant->price,
            ]);

            $orderItem = OrderItem::factory()->create([
                'order_vendor_id' => $orderVendor->id,
                'product_variant_id' => $variant->id,
                'price' => $variant->price,
                'quantity' => 1,
                'total' => $variant->price,
            ]);

            Review::query()->create([
                'product_id' => $product->id,
                'user_id' => $reviewer->id,
                'order_item_id' => $orderItem->id,
                'rating' => fake()->numberBetween(4, 5),
                'comment' => fake()->randomElement([
                    'Produk sesuai deskripsi dan kualitasnya memuaskan.',
                    'Pengemasan rapi, produk datang dalam kondisi baik.',
                    'Bahannya bagus dan sesuai dengan foto produk.',
                    'Worth it untuk harga segini, akan beli lagi.',
                    'Produk nyaman dipakai dan tampilannya menarik.',
                ]),
            ]);
        }
    }
}
