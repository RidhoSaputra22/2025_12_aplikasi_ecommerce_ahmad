<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderVendorStatus;

class OrderVendor extends Model
{
    protected $fillable = [
        'order_id',
        'vendor_id',
        'subtotal',
        'status'
    ];

    protected $casts = [
        'status' => OrderVendorStatus::class,
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }
}
