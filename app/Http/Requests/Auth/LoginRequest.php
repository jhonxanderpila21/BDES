<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Supports `username` as the primary login identifier while remaining backward-compatible if `email` is passed.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('username') && $this->filled('email')) {
            $this->merge([
                'username' => $this->input('email'),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifier = trim((string) $this->input('username'));
        $password   = (string) $this->input('password');
        $errorField = $this->has('email') && ! $this->has('username') ? 'email' : 'username';

        $user = User::where('username', $identifier)
            ->orWhere(function ($query) use ($identifier) {
                if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                    $query->where('email', $identifier);
                }
            })
            ->first();

        // Check if account exists and is currently deactivated
        if ($user && ! $user->is_active) {
            if (Hash::check($password, $user->password)) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    $errorField => 'This account has been deactivated. Please contact your system administrator.',
                ]);
            }
        }

        $authColumn = ($user && $user->email === $identifier && $user->username !== $identifier)
            ? 'email'
            : 'username';

        if (! Auth::attempt([
            $authColumn => $identifier,
            'password'  => $password,
            'is_active' => true,
        ], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $errorField => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds    = RateLimiter::availableIn($this->throttleKey());
        $errorField = $this->has('email') && ! $this->has('username') ? 'email' : 'username';

        throw ValidationException::withMessages([
            $errorField => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }
}
