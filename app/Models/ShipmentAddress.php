<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentAddress extends Model
{
    //

    protected $fillable = [
        'user_id',
        'shipment_id',
        'province',
        'city',
        'district',
        'postal_code',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
