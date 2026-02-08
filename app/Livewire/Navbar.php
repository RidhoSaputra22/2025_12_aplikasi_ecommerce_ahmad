<?php

namespace App\Livewire;

use App\Models\Cart;
use Livewire\Component;

class Navbar extends Component
{

    protected $listeners = [
        'cart-updated-nav' => '$refresh',
        'cart-updated' => '$refresh',
        'payment-proof:uploaded' => '$refresh',
    ];

     public function getCartCountProperty()
    {
        if (!auth()->check()) {
            return 0;
        }

          return Cart::with('cartItems')
            ->where('user_id', auth()->id())
            ->first()
            ?->cartItems()
            ?->count() ?? 0;
    }

    public function getUnreadNotificationCountProperty(): int
    {
        if (!auth()->check()) {
            return 0;
        }

        return auth()->user()->unreadNotifications()->count();
    }



    public function render()
    {


        return view('layouts.navbar');
    }
}
