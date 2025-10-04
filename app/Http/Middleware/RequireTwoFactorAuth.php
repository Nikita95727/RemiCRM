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

        // If on 2FA settings page, allow access
        if ($request->routeIs('two-factor.settings')) {
            return $next($request);
        }

        // If user needs to complete first-time 2FA setup, redirect to settings
        if ($user->needsTwoFactorSetup()) {
            return redirect()->route('two-factor.settings');
        }

        // If user has disabled 2FA, allow access
        if ($user->hasTwoFactorDisabled()) {
            return $next($request);
        }

        // If user has 2FA enabled but not disabled by choice
        if ($user->hasTwoFactorEnabled()) {
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

        return $next($request);
    }
}
