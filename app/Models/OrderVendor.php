<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderVendorStatus;

class OrderVendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'subtotal',
        'status',
        'is_disbursed',
        'disbursed_at',
        'disbursed_by',
        'vendor_confirmed_at',
        'customer_confirmed_at',
    ];

    protected $casts = [
        'status' => OrderVendorStatus::class,
        'is_disbursed' => 'boolean',
        'disbursed_at' => 'datetime',
        'vendor_confirmed_at' => 'datetime',
        'customer_confirmed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function disbursedByUser()
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }
}
