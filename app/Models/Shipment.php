<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ShipmentStatus;

class Shipment extends Model
{
    protected $fillable = [
        'order_vendor_id',
        'courier',
        'service',
        'tracking_number',
        'shipping_cost',
        'status',
        'shipped_at',
        'delivered_at'
    ];

    protected $casts = [
        'status' => ShipmentStatus::class,
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];
}
