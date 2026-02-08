<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add payment proof columns to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('transaction_reference');
            $table->timestamp('confirmed_at')->nullable()->after('paid_at');
            $table->foreignId('confirmed_by')->nullable()->after('confirmed_at')->constrained('users')->nullOnDelete();
        });

        // Update payments.status enum to include 'waiting_confirmation'
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'waiting_confirmation', 'success', 'failed') DEFAULT 'pending'");

        // Update orders.payment_status enum to include 'waiting_confirmation'
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('pending', 'waiting_confirmation', 'paid', 'failed') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Revert orders.payment_status enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending'");

        // Revert payments.status enum
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'success', 'failed') DEFAULT 'pending'");

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('confirmed_by');
            $table->dropColumn(['payment_proof', 'confirmed_at']);
        });
    }
};
