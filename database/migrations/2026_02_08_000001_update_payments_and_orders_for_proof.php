<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        // MODIFY COLUMN is MySQL/MariaDB-specific. Fresh non-MySQL databases
        // already receive the complete enum values from the create migrations.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'waiting_confirmation', 'success', 'failed') DEFAULT 'pending'");
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('pending', 'waiting_confirmation', 'paid', 'failed') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending'");
            DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'success', 'failed') DEFAULT 'pending'");
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('confirmed_by');
            $table->dropColumn(['payment_proof', 'confirmed_at']);
        });
    }
};
