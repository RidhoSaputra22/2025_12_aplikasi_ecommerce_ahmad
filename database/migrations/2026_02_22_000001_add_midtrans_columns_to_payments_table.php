<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('transaction_reference');
            $table->string('snap_redirect_url')->nullable()->after('snap_token');
            $table->string('midtrans_transaction_id')->nullable()->after('snap_redirect_url');
            $table->string('midtrans_payment_type')->nullable()->after('midtrans_transaction_id');
            $table->string('midtrans_va_number')->nullable()->after('midtrans_payment_type');
            $table->string('midtrans_bank')->nullable()->after('midtrans_va_number');
            $table->string('midtrans_store')->nullable()->after('midtrans_bank');
            $table->string('midtrans_payment_code')->nullable()->after('midtrans_store');
            $table->string('midtrans_qr_url')->nullable()->after('midtrans_payment_code');
            $table->string('midtrans_deeplink_url')->nullable()->after('midtrans_qr_url');
            $table->string('midtrans_fraud_status')->nullable()->after('midtrans_deeplink_url');
            $table->json('midtrans_raw_response')->nullable()->after('midtrans_fraud_status');
            $table->timestamp('expired_at')->nullable()->after('paid_at');
        });

        // Update payment_method enum - add 'midtrans'
        // Also ensure payment_gateway can store gateway names
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'snap_token',
                'snap_redirect_url',
                'midtrans_transaction_id',
                'midtrans_payment_type',
                'midtrans_va_number',
                'midtrans_bank',
                'midtrans_store',
                'midtrans_payment_code',
                'midtrans_qr_url',
                'midtrans_deeplink_url',
                'midtrans_fraud_status',
                'midtrans_raw_response',
                'expired_at',
            ]);
        });
    }
};
