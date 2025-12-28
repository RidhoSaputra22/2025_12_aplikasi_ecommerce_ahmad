<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentCourier extends Model
{
    //

    protected $fillable = [
        'name',
        'code',
        'services',
        'price',
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
