<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserStatus;

class User extends Authenticatable
{

    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'foto',
        'description',
        'password',
        'status',
        'last_login_at',
    ];

    protected $casts = [
        'status' => UserStatus::class,
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function userRoles()
    {
        return $this->hasOne(UserRole::class);
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function addresses()
    {
        return $this->hasMany(ShipmentAddress::class);
    }
}
