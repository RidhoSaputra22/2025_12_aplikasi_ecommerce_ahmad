<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.admin.role'  => \App\Http\Middleware\CheckAdminRole::class,
            'check.ship.party.role' => \App\Http\Middleware\CheckShipPartyRole::class,
            'check.vendor.role' => \App\Http\Middleware\CheckVendorRole::class,
            'check.user.role'   => \App\Http\Middleware\CheckUserRole::class,
        ]);

        // Exclude Midtrans webhook dari CSRF verification
        $middleware->validateCsrfTokens(except: [
            'api/midtrans/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
