<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_vendor_id',
        'product_variant_id',
        'quantity',
        'price',
        'total',
    ];

    public function orderVendor()
    {
        return $this->belongsTo(OrderVendor::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
