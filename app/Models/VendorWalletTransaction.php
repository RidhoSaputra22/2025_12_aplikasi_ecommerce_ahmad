<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorWalletTransaction extends Model
{
    //

    protected $fillable = [
        'vendor_wallet_id',
        'type',
        'amount',
        'description',
        'reference_id',
    ];


}
