<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentCourier extends Model
{
    use HasFactory;

    //

    protected $fillable = [
        'user_id',
        'name',
        'code',
        'service',
        'price',
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hasShipPartyAccount(): bool
    {
        return $this->user_id !== null;
    }
}
