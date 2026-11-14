<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    /**
     * OTP validity window in minutes.
     */
    public const EXPIRY_MINUTES = 5;

    /**
     * Minimum cooldown in seconds before a user can request another OTP.
     */
    public const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Maximum wrong verification attempts allowed per OTP before it is invalidated.
     */
    public const MAX_ATTEMPTS = 5;

    public function __construct(
        protected SmsService $smsService
    ) {}

    /**
     * Calculate how many seconds remain in the resend cooldown window for the user.
     */
    public function getCooldownRemainingSeconds(User $user): int
    {
        $latestOtp = $user->otps()
            ->whereNull('verified_at')
            ->latest('created_at')
            ->first();

        if (! $latestOtp || ! $latestOtp->created_at) {
            return 0;
        }

        $elapsed = (int) $latestOtp->created_at->diffInSeconds(now(), false);

        if ($elapsed < 0) {
            return 0;
        }

        return max(0, self::RESEND_COOLDOWN_SECONDS - $elapsed);
    }

    /**
     * Determine whether the user currently has an active, unverified, non-expired OTP.
     */
    public function hasActiveOtp(User $user): bool
    {
        return $user->otps()
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->where('attempts', '<', self::MAX_ATTEMPTS)
            ->exists();
    }

    /**
     * Generate a cryptographically secure 6-digit OTP, store its hash in user_otps,
     * and dispatch it via the SMS gateway.
     *
     * @return array{sent: bool, cooldown: int, expires_at: \Illuminate\Support\Carbon}
     */
    public function generateAndSend(User $user, bool $enforceCooldown = true): array
    {
        if ($enforceCooldown) {
            $cooldown = $this->getCooldownRemainingSeconds($user);
            if ($cooldown > 0) {
                $latest = $user->otps()->whereNull('verified_at')->latest('created_at')->first();

                return [
                    'sent'       => false,
                    'cooldown'   => $cooldown,
                    'expires_at' => $latest?->expires_at ?? now()->addMinutes(self::EXPIRY_MINUTES),
                ];
            }
        }

        $plainOtp  = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(self::EXPIRY_MINUTES);

        DB::transaction(function () use ($user, $plainOtp, $expiresAt) {
            // Invalidate all previous unverified OTPs for this user so they cannot be reused
            $user->otps()
                ->whereNull('verified_at')
                ->delete();

            UserOtp::create([
                'user_id'    => $user->id,
                'otp_hash'   => Hash::make($plainOtp),
                'expires_at' => $expiresAt,
                'attempts'   => 0,
            ]);
        });

        $sent = false;
        if (! empty($user->phone_number)) {
            $appName = config('app.name', 'BDES');
            $message = "[{$appName}] Your verification code is {$plainOtp}. It will expire in " . self::EXPIRY_MINUTES . " minutes. Do not share this code with anyone.";
            $sent    = $this->smsService->send($user->phone_number, $message);
        }

        return [
            'sent'       => $sent,
            'cooldown'   => self::RESEND_COOLDOWN_SECONDS,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Verify a 6-digit OTP submitted by the user.
     *
     * @return array{success: bool, status: string, message: string}
     */
    public function verify(User $user, string $code): array
    {
        $latestOtp = $user->otps()
            ->whereNull('verified_at')
            ->latest('created_at')
            ->first();

        if (! $latestOtp) {
            return [
                'success' => false,
                'status'  => 'missing',
                'message' => 'No active verification code was found. Please request a new OTP.',
            ];
        }

        if ($latestOtp->isExpired()) {
            return [
                'success' => false,
                'status'  => 'expired',
                'message' => 'Your OTP has expired. Please click Resend OTP to receive a new 6-digit code.',
            ];
        }

        if ($latestOtp->attempts >= self::MAX_ATTEMPTS) {
            return [
                'success' => false,
                'status'  => 'locked',
                'message' => 'Maximum verification attempts exceeded for this code. Please request a new OTP.',
            ];
        }

        if (! Hash::check($code, $latestOtp->otp_hash)) {
            $latestOtp->increment('attempts');
            $remaining = max(0, self::MAX_ATTEMPTS - $latestOtp->attempts);

            if ($remaining === 0) {
                return [
                    'success' => false,
                    'status'  => 'locked',
                    'message' => 'Invalid OTP. Maximum attempts reached—please request a new OTP code.',
                ];
            }

            return [
                'success' => false,
                'status'  => 'invalid',
                'message' => "Invalid verification code. You have {$remaining} attempt(s) remaining.",
            ];
        }

        DB::transaction(function () use ($user, $latestOtp) {
            $now = now();

            $latestOtp->update([
                'verified_at' => $now,
            ]);

            // Remove any other lingering unverified OTP rows
            $user->otps()
                ->where('id', '!=', $latestOtp->id)
                ->whereNull('verified_at')
                ->delete();

            $user->forceFill([
                'phone_verified_at' => $now,
            ])->save();
        });

        return [
            'success' => true,
            'status'  => 'verified',
            'message' => 'Phone number verified successfully.',
        ];
    }
}

