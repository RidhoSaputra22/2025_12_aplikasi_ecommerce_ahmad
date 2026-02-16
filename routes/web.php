<?php

use App\Http\Controllers\HomeController;
use App\Livewire\User\Auth\Login;
use App\Livewire\User\Auth\Regist;
use App\Livewire\User\Cart\Cart;
use App\Livewire\User\Dashboard\Dashboard;
use App\Livewire\User\Home\Welcome;
use App\Livewire\User\Products\Cari;
use App\Livewire\User\Products\Detail;
use App\Livewire\Vendor\Dashboard\Dashboard as VendorDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class)->name('welcome');

Route::get('/produk', Cari::class)->name('produk.cari');
Route::get('/produk/detail/{slug}', Detail::class)->name('produk.detail');
Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');
Route::get('/keranjang', Cart::class)->name('cart.index');

Route::middleware('auth')->group(function () {
    Route::get('/user/dashboard', Dashboard::class)->name('user.dashboard');
    Route::get('/vendor/dashboard', VendorDashboard::class)->name('vendor.dashboard');
});

Route::get('/user/login', Login::class)->name('user.login');
Route::get('/login', Login::class)->name('login');
Route::get('/user/logout', function () {
    auth()->logout();

    return redirect()->route('welcome');
})->name('user.logout');
Route::get('/user/register', Regist::class)->name('user.register');
