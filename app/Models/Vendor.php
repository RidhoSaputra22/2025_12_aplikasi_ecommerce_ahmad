<?php

namespace App\Models;

use App\Enums\VendorStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'status',
    ];

    protected $casts = [
        'status' => VendorStatus::class,
    ];

    protected static function booted()
    {
        static::creating(function ($vendor) {
            $base = Str::slug($vendor->slug ?: $vendor->store_name) ?: 'vendor';
            $slug = $base;
            $suffix = 2;

            while (static::query()->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix++;
            }

            $vendor->slug = $slug;
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
