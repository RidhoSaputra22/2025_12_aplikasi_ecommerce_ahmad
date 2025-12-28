<?php

namespace App\Filament\Vendor\Resources\Carts\Pages;

use App\Models\Cart;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Vendor\Resources\Carts\CartResource;

class CreateCart extends CreateRecord
{
    protected static string $resource = CartResource::class;

    protected function handleRecordCreation(array $data): Model
    {

        $cart = Cart::create([
            'user_id' => $data['user_id'],
        ]);

        foreach ($data['items'] as $item) {
            $productVariant = ProductVariant::find($item['product_variant_id']);
            $item['price'] = $productVariant->price;

            $cart->cartItems()->create([
                'cart_id' => $cart->id,
                'product_variant_id' => $item['product_variant_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }



        return $cart;
    }
}
