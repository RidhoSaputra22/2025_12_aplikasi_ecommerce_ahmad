<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->status === UserStatus::Active
            && $user->role()->where('name', 'admin')->exists()
        ) {
            return $next($request);
        }

        return redirect()->route('filament.admin.auth.login');
    }
}
