<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * Only authenticated users with the 'customer' role may access
     * the protected customer routes (/user/* and /keranjang).
     * Admins are redirected to the admin panel, vendors to their dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Unauthenticated users → redirect to login
        if (! $user) {
            return redirect()->route('user.login');
        }

        // Admin → should use /admin panel, not customer routes
        if ($user->role()->where('name', 'admin')->exists()) {
            return redirect('/admin');
        }

        // Vendor → should use /vendor/dashboard, not customer routes
        if ($user->role()->where('name', 'vendor')->exists()) {
            return redirect()->route('vendor.dashboard');
        }

        return $next($request);
    }
}
