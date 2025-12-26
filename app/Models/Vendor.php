<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\VendorStatus;

class Vendor extends Model
{
    protected $fillable = [
        'user_id',
        'store_name',
        'slug',
        'description',
        'logo',
        'banner',
        'is_verified',
        'rating',
        'status'
    ];

    protected $casts = [
        'status' => VendorStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function addresses()
    {
        return $this->hasMany(VendorAddress::class);
    }

    public function wallet()
    {
        return $this->hasOne(VendorWallet::class);
    }
}
