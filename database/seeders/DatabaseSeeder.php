<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
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

        $this->call([
            // CategorySeeder::class,
            ProductSeeder::class,
            // ReviewSeeder::class,
        ]);

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
                'email' => 'customer@gmail.com',
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
    }
}
