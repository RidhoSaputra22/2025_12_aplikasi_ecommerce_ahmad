<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentCourier extends Model
{
    use HasFactory;

    //

    protected $fillable = [
        'name',
        'code',
        'service',
        'price',
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
