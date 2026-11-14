<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneVerifiedAndPasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * Enforces two-step onboarding for newly provisioned accounts:
     * 1. Phone number OTP verification (if phone_number is set and phone_verified_at is null)
     * 2. First-time temporary password change (if must_change_password is true)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Always allow logout
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        // Step 1: Require Phone OTP Verification
        if ($user->needsPhoneVerification()) {
            if (! $request->routeIs('otp.*')) {
                return redirect()->route('otp.notice');
            }

            return $next($request);
        }

        // Step 2: Require First-Time Password Change
        if ($user->needsPasswordChange()) {
            if (! $request->routeIs('password.first-time', 'password.first-time.update')) {
                return redirect()->route('password.first-time');
            }

            return $next($request);
        }

        return $next($request);
    }
}

