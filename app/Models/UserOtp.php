<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOtp extends Model
{
    use HasFactory;

    protected $table = 'user_otps';

    protected $fillable = [
        'user_id',
        'otp_hash',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected $hidden = [
        'otp_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'  => 'datetime',
            'verified_at' => 'datetime',
            'attempts'    => 'integer',
        ];
    }

    /**
     * The user that owns the OTP record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine whether this OTP has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Determine whether this OTP has already been verified/used.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}

