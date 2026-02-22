<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_gateway',
        'amount',
        'status',
        'transaction_reference',
        'payment_proof',
        'paid_at',
        'confirmed_at',
        'confirmed_by',
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
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'expired_at' => 'datetime',
        'midtrans_raw_response' => 'array',
    ];

    /**
     * Apakah pembayaran menggunakan Midtrans.
     */
    public function isMidtrans(): bool
    {
        return $this->payment_gateway === 'midtrans';
    }

    /**
     * Apakah pembayaran manual (upload bukti).
     */
    public function isManual(): bool
    {
        return $this->payment_method === 'manual';
    }

    /**
     * Apakah snap token sudah expired.
     */
    public function isSnapExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function confirmedByUser()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
