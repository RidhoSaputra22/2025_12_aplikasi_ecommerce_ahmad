<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBankAccount extends Model
{
    use HasFactory;

    //
    protected $fillable = [
        'vendor_id',
        'bank_name',
        'account_number',
        'account_holder',
        'is_active',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
