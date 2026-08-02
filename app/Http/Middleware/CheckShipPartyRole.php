<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckShipPartyRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('login')) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (
            $user->status === UserStatus::Active
            && $user->role()->where('name', 'pihak_kapal')->exists()
            && $user->managedShipmentCourier()->exists()
        ) {
            return $next($request);
        }

        abort(403);
    }
}
