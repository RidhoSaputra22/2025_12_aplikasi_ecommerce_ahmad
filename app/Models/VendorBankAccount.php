<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorBankAccount extends Model
{
    //

    protected $fillable = [
        'vendor_id',
        'bank_name',
        'account_number',
        'account_holder',
        'is_active',
    ];
}
