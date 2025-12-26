<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/etalase', [HomeController::class, 'etalase'])->name('etalase');
Route::get('/etalase/detail/{slug}', [HomeController::class, 'etalaseDetail'])->name('etalase.detail');
Route::get('/produk', [HomeController::class, 'produk'])->name('produk');
Route::get('/produk/detail/{slug}', [HomeController::class, 'produkDetail'])->name('produk.detail');
Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');
Route::get('/keranjang', [HomeController::class, 'keranjang'])->name('keranjang');