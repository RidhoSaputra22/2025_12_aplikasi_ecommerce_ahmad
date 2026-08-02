<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShipmentAddress;
use App\Models\ShipmentCourier;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            ['name' => 'customer'],
            ['name' => 'pihak_kapal'],
        ]);

        User::insert([
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'foto' => null,
                'description' => null,
                'password' => bcrypt('password'),
            ],

            [
                'name' => 'Vendor',
                'email' => 'vendor@gmail.com',
                'foto' => null,
                'description' => null,
                'password' => bcrypt('password'),
            ],

            [
                'name' => 'Customer',
                'email' => 'customer@gmail.com',
                'foto' => null,
                'description' => null,
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Pihak Kapal',
                'email' => 'kapal@gmail.com',
                'foto' => null,
                'description' => null,
                'password' => bcrypt('password'),
            ],

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
            ],
            [
                'user_id' => 4,
                'role_id' => 4,
            ],
        ]);

        Vendor::insert([
            [
                'user_id' => 2,
                'store_name' => 'Toko Vendor',
                'description' => 'Deskripsi toko vendor',
                'logo' => null,
                'banner' => null,
                'slug' => 'toko-vendor',
            ],
        ]);

        // Product::factory()
        //     ->count(10)
        //     ->has(
        //         ProductImage::factory()
        //             ->count(1)
        //     )
        //     ->has(
        //         ProductVariant::factory()->count(1)
        //     )
        //     ->create([
        //         'vendor_id' => 1,
        //     ]);

        Shipment::factory(5)->create();

        ShipmentCourier::factory()->create([
            'name' => 'Pihak Kapal Express',
            'code' => 'KAPAL-001',
            'service' => 'SEA-REG',
            'price' => 20000,
            'user_id' => 4,
        ]);

        ShipmentCourier::factory(2)->create();

        ShipmentAddress::factory(10)->create(
            [
                'user_id' => 3,
            ]
        );

        $this->call([
            CategorySeeder::class,
            AdminBankAccountSeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,
        ]);

    }
}
