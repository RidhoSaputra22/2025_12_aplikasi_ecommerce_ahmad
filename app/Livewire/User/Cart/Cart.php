<?php

namespace App\Livewire\User\Cart;

use Livewire\Component;

class Cart extends Component
{
    public function render()
    {
        return view('user.cart.cart')->extends('layouts.app')->section('content');
    }
}
