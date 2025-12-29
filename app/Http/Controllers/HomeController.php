<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductVariant;

class HomeController extends Controller
{
    //

    public function index()
    {
        $produk = Product::with(['productImages', 'productVariants'])
            ->where('status', 'ACTIVE')
            ->latest()
            ->take(5)
            ->get();

        return view('welcome', compact('produk'));
    }

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