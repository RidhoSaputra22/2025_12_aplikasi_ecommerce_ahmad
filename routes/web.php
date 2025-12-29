<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserAuthController;

Route::get('/', [HomeController::class, 'index'])->name('welcome');


Route::get('/produk', [HomeController::class, 'produk'])->name('produk');
Route::get('/produk/detail/{slug}', [HomeController::class, 'produkDetail'])->name('produk.detail');
Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');
Route::get('/keranjang', [HomeController::class, 'keranjang'])->name('keranjang');


Route::get('/user/login', [UserAuthController::class, 'login'])->name('user.login');
Route::get('/user/register', [UserAuthController::class, 'register'])->name('user.register');