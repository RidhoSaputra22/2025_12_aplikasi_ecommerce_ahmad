<?php

namespace App\Livewire;

use App\Models\Cart;
use Livewire\Component;

class Navbar extends Component
{

    protected $listeners = [
        'cartUpdated' => '$refresh',
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




    public function render()
    {


        return view('layouts.navbar');
    }
}
