<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\MidtransService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind PaymentGatewayInterface ke MidtransService
        // Untuk mengganti payment gateway, cukup ubah binding ini.
        $this->app->bind(PaymentGatewayInterface::class, MidtransService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
