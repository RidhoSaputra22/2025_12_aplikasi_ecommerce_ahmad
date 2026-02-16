<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_vendors', function (Blueprint $table) {
            $table->boolean('is_disbursed')->default(false)->after('status');
            $table->timestamp('disbursed_at')->nullable()->after('is_disbursed');
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete()->after('disbursed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_vendors', function (Blueprint $table) {
            $table->dropForeign(['disbursed_by']);
            $table->dropColumn(['is_disbursed', 'disbursed_at', 'disbursed_by']);
        });
    }
};
