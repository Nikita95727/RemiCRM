<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIsConfirmed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If user is not authenticated, let the request continue
        if (!$user) {
            return $next($request);
        }

        // If user is already on the 2FA challenge page, don't redirect
        if ($request->routeIs('two-factor.login')) {
            return $next($request);
        }

        // If user has 2FA enabled but not confirmed in this session
        if ($user->hasEnabledTwoFactorAuthentication() && !$request->session()->has('two_factor_confirmed')) {
            return redirect()->route('two-factor.login');
        }

        return $next($request);
    }
}
