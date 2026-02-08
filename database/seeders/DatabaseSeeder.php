<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShipmentAddress;
use App\Models\ShipmentCourier;
use App\Models\User;
use App\Models\UserRole;


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
        ]);

        User::insert([
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'foto' => null,
                'description' => null,
                'password' => bcrypt('admin'),
            ],

            [
                'name' => 'Vendor',
                'email' => 'vendor@gmail.com',
                'foto' => null,
                'description' => null,
                'password' => bcrypt('vendor'),
            ],

            [
                'name' => 'Customer',
                'email' => 'customer@gmail.com',
                'foto' => null,
                'description' => null,
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



        Shipment::factory(5)->create();

        ShipmentCourier::factory(3)->create();

        ShipmentAddress::factory(10)->create(
            [
                'user_id' => 3,
            ]
        );





         $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,
        ]);

    }
}
