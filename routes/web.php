<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\PaymentRedirectController;
use App\Http\Controllers\Vendor\OrderReportPdfController;
use App\Livewire\User\Auth\Login;
use App\Livewire\User\Auth\Regist;
use App\Livewire\User\Cart\Cart;
use App\Livewire\User\Dashboard\Dashboard;
use App\Livewire\User\Home\Welcome;
use App\Livewire\User\Payment\PaymentPage;
use App\Livewire\User\Products\Cari;
use App\Livewire\User\Products\Detail;
use App\Livewire\ShipParty\Dashboard\Dashboard as ShipPartyDashboard;
use App\Livewire\Vendor\Dashboard\Dashboard as VendorDashboard;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class)->name('welcome');

Route::get('/produk', Cari::class)->name('produk.cari');
Route::get('/produk/detail/{slug}', Detail::class)->name('produk.detail');
Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');
// Customer-only routes — harus login & memiliki role 'customer'
Route::middleware(['auth', 'check.user.role'])->group(function () {
    Route::get('/keranjang', Cart::class)->name('cart.index');
    Route::get('/user/dashboard', Dashboard::class)->name('user.dashboard');

    // Payment
    Route::get('/pembayaran/finish', [PaymentRedirectController::class, 'finish'])->name('payment.finish');
    Route::get('/pembayaran/{orderId}', PaymentPage::class)->name('payment.page');
});

// Vendor-only routes — harus login & memiliki role 'vendor'
Route::middleware(['auth', 'check.vendor.role'])->group(function () {
    Route::get('/vendor/dashboard', VendorDashboard::class)->name('vendor.dashboard');
    Route::get('/vendor/reports/orders/pdf', OrderReportPdfController::class)->name('vendor.orders.report.pdf');
});

Route::middleware(['auth', 'check.ship.party.role'])->group(function () {
    Route::get('/pihak-kapal/dashboard', ShipPartyDashboard::class)->name('ship-party.dashboard');
});

// Midtrans Webhook (tanpa auth, tanpa CSRF)
Route::post('/api/midtrans/notification', [MidtransWebhookController::class, 'notification'])
    ->name('midtrans.notification')
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::get('/user/login', Login::class)->name('user.login');
Route::get('/login', Login::class)->name('login');
Route::post('/user/logout', function (Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('user.login');
})->middleware('auth')->name('user.logout');
Route::get('/user/register', Regist::class)->name('user.register');
