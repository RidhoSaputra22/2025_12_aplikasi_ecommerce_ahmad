<?php

namespace App\Models;

use App\Enums\VendorStatus;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

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

    protected static function booted()
    {
        static::creating(function ($vendor) {
            if (empty($vendor->slug)) {
                $vendor->slug = Str::slug($vendor->store_name);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function vendorAddresses()
    {
        return $this->hasMany(VendorAddress::class);
    }

    public function vendorWallet()
    {
        return $this->hasOne(VendorWallet::class);
    }

    public function vendorBankAccounts()
    {
        return $this->hasMany(VendorBankAccount::class);
    }
}
