<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVendorRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow the vendor authentication endpoints to be accessed without the vendor role
        // to avoid redirect loops (ERR_TOO_MANY_REDIRECTS).
        if ($request->routeIs('filament.vendor.auth.*')) {
            return $next($request);
        }

        $user = $request->user();

        // Guests should be redirected to the vendor login page.
        if (! $user) {
            return redirect()->route('filament.vendor.auth.login');
        }

        // Only vendors can access protected vendor routes.
        if ($user->role()->where('name', 'vendor')->exists()) {
            return $next($request);
        }

        abort(403);
    }
}
