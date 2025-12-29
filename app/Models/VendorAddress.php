<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAddress extends Model
{
    use HasFactory;

    //

    protected $fillable = [
        'vendor_id',
        'province',
        'city',
        'district',
        'postal_code',
        'address',
        'is_primary',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
