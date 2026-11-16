<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Step 1: If account has a registered phone number that is not yet verified, initiate OTP verification
        if ($user && $user->needsPhoneVerification()) {
            if (! $this->otpService->hasActiveOtp($user) || $this->otpService->getCooldownRemainingSeconds($user) === 0) {
                $this->otpService->generateAndSend($user);
            }

            return redirect()->route('otp.notice')
                ->with('status', 'A 6-digit verification code has been sent to your registered phone number.');
        }

        // Step 2: If account is using a temporary password, require first-time password change
        if ($user && $user->needsPasswordChange()) {
            return redirect()->route('password.first-time')
                ->with('status', 'Please set a new password to complete your first-time login.');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
