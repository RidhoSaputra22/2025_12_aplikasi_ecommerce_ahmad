<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderVendor;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShipmentAddress;
use App\Models\ShipmentCourier;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Vendor;
use App\Models\VendorAddress;
use App\Models\VendorBankAccount;
use App\Models\VendorWallet;
use App\Models\VendorWalletTransaction;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Factory validation: buat 1 record per model (ringan), dengan relasi terhubung.

        Role::insert([
            ['name' => 'admin'],
            ['name' => 'vendor'],
            ['name' => 'user'],
        ]);

        User::insert([
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('admin'),
            ],

            [
                'name' => 'Vendor',
                'email' => 'vendor@gmail.com',
                'password' => bcrypt('vendor'),
            ],

            [
                'name' => 'Customer',
                'email' => 'customer@example.com',
                'password' => bcrypt('customer'),
            ]

        ]);

        UserRole::insert([
            [
                'user_id' => 1,
                'role_id' => 1,
            ],
            [
                'user_id' => 2,
                'role_id' => 2,
            ],
            [
                'user_id' => 3,
                'role_id' => 3,
            ]
        ]);

        Product::factory()
            ->count(10)
            ->has(ProductImage::factory()
                ->count(1))
            ->has(ProductVariant::factory()->count(2))
            ->create();

        Category::factory()
            ->count(5)
            ->has(Product::factory()->count(2))

            ->create()
        ;
    }
}
