<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function __construct(
        protected SmsService $smsService
    ) {}

    /**
     * Display the password reset link request view (Phone Number based).
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request via Phone Number SMS.
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
            'phone_number' => ['required', 'string', 'regex:/^\+639\d{9}$/'],
        ], [
            'phone_number.required' => 'Please enter your registered Philippine mobile number.',
            'phone_number.regex'    => 'Please enter a valid Philippine mobile number (e.g., +639171234567 or 09171234567).',
        ]);

        $user = User::where('phone_number', $normalizedPhone)->first();

        if (! $user) {
            return back()
                ->withInput($request->only('phone_number'))
                ->withErrors(['phone_number' => 'We could not find an account registered with that phone number.']);
        }

        // Generate a secure reset token and store its hash in password_reset_tokens keyed by phone_number
        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->phone_number],
            [
                'token'      => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        $resetUrl = route('password.reset', [
            'token'        => $plainToken,
            'phone_number' => $user->phone_number,
        ]);

        $appName = config('app.name', 'BDES');
        $smsMessage = "[{$appName}] Password reset link for {$user->username}: {$resetUrl} (Expires in 60 minutes).";

        $sent = $this->smsService->send($user->phone_number, $smsMessage);

        $response = back()->with(
            'status',
            "A password reset link has been sent via SMS to your phone number ({$user->masked_phone})."
        );

        // In local/demo mode when no external SMS gateway API key is configured, surface the reset link for testing
        if (! app()->environment('production') && ! $sent) {
            $response->with('demo_reset_link', $resetUrl);
        }

        return $response;
    }
}
