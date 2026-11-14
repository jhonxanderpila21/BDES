<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class FirstTimePasswordController extends Controller
{
    /**
     * Show the First-Time Password Change form for newly created accounts.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->needsPhoneVerification()) {
            return redirect()->route('otp.notice');
        }

        if (! $user->needsPasswordChange()) {
            return redirect()->route('dashboard');
        }

        return view('auth.first-time-password');
    }

    /**
     * Validate and update the user's password, completing first-time login onboarding.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->needsPhoneVerification()) {
            return redirect()->route('otp.notice');
        }

        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers(),
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    if (
                        $value === User::DEFAULT_TEMP_PASSWORD ||
                        strtolower((string) $value) === strtolower(User::DEFAULT_TEMP_PASSWORD) ||
                        Hash::check((string) $value, $user->password)
                    ) {
                        $fail('Your new password cannot be the same as your temporary password.');
                    }
                },
            ],
        ], [
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $user->forceFill([
            'password'             => Hash::make($validated['password']),
            'must_change_password' => false,
        ])->save();

        return redirect()->route('dashboard')
            ->with('status', 'Your password has been updated successfully. Welcome to BDES!');
    }
}

