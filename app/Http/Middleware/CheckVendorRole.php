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
        // Allow access to the regular login page to avoid redirect loops
        if ($request->routeIs('login')) {
            return $next($request);
        }

        $user = $request->user();

        // Guests should be redirected to the login page.
        if (! $user) {
            return redirect()->route('login');
        }

        // Only vendors can access protected vendor routes.
        if ($user->role()->where('name', 'vendor')->exists()) {
            return $next($request);
        }

        abort(403);
    }
}
