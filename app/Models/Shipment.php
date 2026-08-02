<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ShipmentStatus;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_vendor_id',
        'shipment_address_id',
        'shipment_courier_id',
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

    public function orderVendor()
    {
        return $this->belongsTo(OrderVendor::class);
    }

    public function shipmentAddress()
    {
        return $this->belongsTo(ShipmentAddress::class);
    }

    public function shipmentCourier()
    {
        return $this->belongsTo(ShipmentCourier::class);
    }

    public function canAutoShipToShipParty(): bool
    {
        return $this->shipmentCourier?->hasShipPartyAccount() === true;
    }
}
