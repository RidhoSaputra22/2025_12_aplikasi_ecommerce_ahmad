<?php

use App\Livewire\User\Auth\Login;
use Illuminate\Support\Facades\Route;
use App\Livewire\User\Products\Detail;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Livewire\User\Auth\Regist;

Route::get('/', [HomeController::class, 'index'])->name('welcome');


Route::get('/produk', [ProductController::class, 'produk'])->name('produk');
Route::get('/produk/detail/{slug}', Detail::class)->name('produk.detail');
Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');
Route::get('/keranjang', [HomeController::class, 'keranjang'])->name('keranjang');


Route::get('/user/login', Login::class)->name('user.login');
Route::get('/user/register', Regist::class)->name('user.register');
