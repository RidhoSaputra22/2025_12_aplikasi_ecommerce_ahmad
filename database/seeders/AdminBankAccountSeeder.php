<?php

namespace Database\Seeders;

use App\Models\AdminBankAccount;
use Illuminate\Database\Seeder;

class AdminBankAccountSeeder extends Seeder
{
    public function run(): void
    {
        AdminBankAccount::create([
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Toko Desa',
            'is_active' => true,
        ]);

        AdminBankAccount::create([
            'bank_name' => 'BRI',
            'account_number' => '0987654321',
            'account_holder' => 'Toko Desa',
            'is_active' => true,
        ]);
    }
}
