<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan status 'delivered' ke order_vendors:
     * pending → processed → shipped → delivered (vendor konfirmasi tiba)
     *                                 ↓
     *                          completed (customer konfirmasi terima + wallet credit)
     *
     * Juga menambahkan kolom audit trail untuk konfirmasi vendor & customer.
     */
    public function up(): void
    {
        // Alter ENUM column untuk menambahkan value 'delivered'
        DB::statement("ALTER TABLE order_vendors MODIFY COLUMN status ENUM('pending','processed','shipped','delivered','completed') NOT NULL DEFAULT 'pending'");

        Schema::table('order_vendors', function (Blueprint $table) {
            $table->timestamp('vendor_confirmed_at')->nullable()->after('status')
                ->comment('Waktu vendor mengkonfirmasi pesanan tiba di tujuan');
            $table->timestamp('customer_confirmed_at')->nullable()->after('vendor_confirmed_at')
                ->comment('Waktu customer mengkonfirmasi pesanan diterima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_vendors', function (Blueprint $table) {
            $table->dropColumn(['vendor_confirmed_at', 'customer_confirmed_at']);
        });

        DB::statement("ALTER TABLE order_vendors MODIFY COLUMN status ENUM('pending','processed','shipped','completed') NOT NULL DEFAULT 'pending'");
    }
};
