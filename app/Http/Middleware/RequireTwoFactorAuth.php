<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactorAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If no user is authenticated, let other middleware handle it
        if (!$user) {
            return $next($request);
        }

        // If user doesn't have 2FA enabled, allow access
        if (!$user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        // If already on the 2FA challenge page, allow access
        if ($request->routeIs('two-factor.challenge')) {
            return $next($request);
        }

        // If 2FA is already verified in this session, allow access
        if ($request->session()->get('two_factor_verified', false)) {
            return $next($request);
        }

        // Redirect to 2FA challenge page
        return redirect()->route('two-factor.challenge');
    }
}
