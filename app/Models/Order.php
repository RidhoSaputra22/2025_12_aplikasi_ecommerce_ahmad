<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;
use App\Enums\OrderPaymentStatus;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'status',
        'payment_status'
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => OrderPaymentStatus::class,
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            // Generate a unique order number
            $order->order_number = 'ORD-' . strtoupper(uniqid());
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderVendors()
    {
        return $this->hasMany(OrderVendor::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
