<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_vendors', function (Blueprint $table) {
            $table->decimal('admin_fee_percentage', 5, 2)->nullable()->after('subtotal');
            $table->decimal('admin_fee_amount', 15, 2)->nullable()->after('admin_fee_percentage');
            $table->decimal('vendor_payout_amount', 15, 2)->nullable()->after('admin_fee_amount');
        });

        DB::table('order_vendors')
            ->where('is_disbursed', true)
            ->update([
                'admin_fee_percentage' => 0,
                'admin_fee_amount' => 0,
                'vendor_payout_amount' => DB::raw('subtotal'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_vendors', function (Blueprint $table) {
            $table->dropColumn([
                'admin_fee_percentage',
                'admin_fee_amount',
                'vendor_payout_amount',
            ]);
        });
    }
};
