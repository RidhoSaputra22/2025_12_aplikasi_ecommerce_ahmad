<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function etalase()
    {
        return view('etalase');
    }

    public function produk()
    {
        return view('produk');
    }

    public function tentang()
    {
        return view('tentang');
    }

    public function etalaseDetail($slug)
    {
        return view('etalase-detail', compact('slug'));
    }

    public function produkDetail($slug)
    {
        return view('produk-detail', compact('slug'));
    }

    public function keranjang()
    {
        return view('keranjang');
    }
}