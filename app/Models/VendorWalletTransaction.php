<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\VendorWalletTransactionType;

class VendorWalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_wallet_id',
        'type',
        'amount',
        'description',
        'reference_id',
    ];

    protected $casts = [
        'type' => VendorWalletTransactionType::class,
    ];

    public function vendorWallet()
    {
        return $this->belongsTo(VendorWallet::class);
    }
}
