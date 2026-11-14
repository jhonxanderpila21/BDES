<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhoneOtpController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Display the 6-digit phone OTP verification page.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->needsPhoneVerification()) {
            if ($user->needsPasswordChange()) {
                return redirect()->route('password.first-time');
            }

            return redirect()->route('dashboard');
        }

        // If user has no active unexpired OTP and is outside cooldown, automatically issue one
        if (! $this->otpService->hasActiveOtp($user) && $this->otpService->getCooldownRemainingSeconds($user) === 0) {
            $this->otpService->generateAndSend($user);
        }

        $latestOtp = $user->otps()->whereNull('verified_at')->latest('created_at')->first();
        $expiresInSeconds = $latestOtp && ! $latestOtp->isExpired()
            ? max(0, (int) now()->diffInSeconds($latestOtp->expires_at, false))
            : 0;

        return view('auth.verify-otp', [
            'maskedPhone'      => $user->masked_phone,
            'cooldownSeconds'  => $this->otpService->getCooldownRemainingSeconds($user),
            'expiresInSeconds' => $expiresInSeconds,
        ]);
    }

    /**
     * Verify the submitted 6-digit OTP code.
     */
    public function verify(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->needsPhoneVerification()) {
            return $user->needsPasswordChange()
                ? redirect()->route('password.first-time')
                : redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ], [
            'otp.required' => 'Please enter the 6-digit verification code sent to your phone.',
            'otp.digits'   => 'The verification code must be an exact 6-digit number.',
        ]);

        $result = $this->otpService->verify($user, $validated['otp']);

        if (! $result['success']) {
            return back()->withErrors([
                'otp' => $result['message'],
            ]);
        }

        if ($user->fresh()->needsPasswordChange()) {
            return redirect()->route('password.first-time')
                ->with('status', 'Phone number verified! Please change your temporary password to continue.');
        }

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('status', 'Phone number verified successfully.');
    }

    /**
     * Resend a fresh 6-digit OTP to the user's registered phone number with cooldown protection.
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->needsPhoneVerification()) {
            return $user->needsPasswordChange()
                ? redirect()->route('password.first-time')
                : redirect()->route('dashboard');
        }

        $cooldown = $this->otpService->getCooldownRemainingSeconds($user);
        if ($cooldown > 0) {
            return back()->withErrors([
                'otp' => "Please wait {$cooldown} seconds before requesting another verification code.",
            ]);
        }

        $result = $this->otpService->generateAndSend($user);

        if ($result['sent']) {
            return back()->with('status', 'A new 6-digit verification code has been sent to your registered phone number.');
        }

        return back()->with('status', 'A new 6-digit verification code has been generated. Please check your SMS messages.');
    }
}

