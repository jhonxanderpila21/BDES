<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request using the SMS reset link token and phone number.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rawPhone = (string) $request->input('phone_number', '');
        $normalizedPhone = User::normalizePhilippinePhone($rawPhone);

        $request->merge([
            'phone_number' => $normalizedPhone,
        ]);

        $request->validate([
            'token'        => ['required', 'string'],
            'phone_number' => ['required', 'string', 'regex:/^\+639\d{9}$/'],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'phone_number.required' => 'Please enter your registered phone number.',
            'phone_number.regex'    => 'Please enter a valid Philippine mobile number (e.g., +639171234567 or 09171234567).',
        ]);

        $user = User::where('phone_number', $normalizedPhone)->first();

        if (! $user) {
            return back()
                ->withInput($request->only('phone_number'))
                ->withErrors(['phone_number' => 'We could not find a user with that phone number.']);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $user->phone_number)
            ->first();

        if (! $record || ! Hash::check((string) $request->input('token'), $record->token)) {
            return back()
                ->withInput($request->only('phone_number'))
                ->withErrors(['phone_number' => 'This password reset link is invalid or has already been used.']);
        }

        $expireMinutes = (int) config('auth.passwords.users.expire', 60);
        if ($record->created_at && Carbon::parse($record->created_at)->addMinutes($expireMinutes)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $user->phone_number)->delete();

            return back()
                ->withInput($request->only('phone_number'))
                ->withErrors(['phone_number' => 'This password reset link has expired. Please request a new one.']);
        }

        $user->forceFill([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
            'phone_verified_at'    => $user->phone_verified_at ?? now(),
            'remember_token'       => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $user->phone_number)->delete();

        event(new PasswordReset($user));

        return redirect()->route('login')
            ->with('status', 'Your password has been reset successfully! You can now sign in with your new password.');
    }
}
