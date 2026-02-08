<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentAddress extends Model
{
    use HasFactory;

    //

    protected $fillable = [
        'user_id',
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

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
