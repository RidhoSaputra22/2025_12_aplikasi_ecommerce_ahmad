<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->updateOrInsert(
            ['name' => 'pihak_kapal'],
            ['updated_at' => now(), 'created_at' => now()],
        );
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'pihak_kapal')
            ->delete();
    }
};
