<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'status',
        'payment_status'
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

    public function vendors()
    {
        return $this->hasMany(OrderVendor::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}