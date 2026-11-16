<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, \App\Traits\Archivable;

    public const DEFAULT_TEMP_PASSWORD = 'temp_pass_123';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'username',
        'email',
        'phone_number',
        'phone_verified_at',
        'password',
        'must_change_password',
        'role',
        'is_active',
        'deleted_by',
        'deletion_reason',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->first_name || $user->last_name) {
                $parts = array_filter([
                    $user->first_name,
                    $user->middle_name,
                    $user->last_name,
                ]);
                $fullName = implode(' ', $parts);
                if ($user->suffix) {
                    $fullName .= ' ' . $user->suffix;
                }
                $user->name = trim($fullName);
            }
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Default attribute values (mirrors DB defaults so new in-memory models are consistent).
     */
    protected $attributes = [
        'is_active'            => true,
        'must_change_password' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'phone_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'must_change_password' => 'boolean',
            'is_active'            => 'boolean',
        ];
    }

    /**
     * OTP records generated for this user.
     */
    public function otps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserOtp::class);
    }

    /**
     * Normalize a Philippine mobile number into +639XXXXXXXXX format.
     */
    public static function normalizePhilippinePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $cleaned = preg_replace('/[\s\-\(\)]+/', '', trim($phone));

        if ($cleaned === '') {
            return null;
        }

        if (preg_match('/^09(\d{9})$/', $cleaned, $matches)) {
            return '+639' . $matches[1];
        }

        if (preg_match('/^639(\d{9})$/', $cleaned, $matches)) {
            return '+639' . $matches[1];
        }

        if (preg_match('/^9(\d{9})$/', $cleaned, $matches)) {
            return '+639' . $matches[1];
        }

        if (preg_match('/^\+639(\d{9})$/', $cleaned)) {
            return $cleaned;
        }

        return $cleaned;
    }

    /**
     * Determine whether the user's phone number is verified (or not required for legacy accounts without phone).
     */
    public function hasVerifiedPhone(): bool
    {
        if (empty($this->phone_number)) {
            return true;
        }

        return $this->phone_verified_at !== null;
    }

    /**
     * Determine whether the user must complete phone OTP verification before accessing the system.
     */
    public function needsPhoneVerification(): bool
    {
        return ! empty($this->phone_number) && $this->phone_verified_at === null;
    }

    /**
     * Determine whether the user must change their temporary password before accessing the dashboard.
     */
    public function needsPasswordChange(): bool
    {
        return (bool) $this->must_change_password;
    }

    /**
     * Return a masked representation of the user's phone number (e.g. +639*****1234).
     */
    public function getMaskedPhoneAttribute(): string
    {
        if (empty($this->phone_number)) {
            return 'N/A';
        }

        $phone = $this->phone_number;
        $length = strlen($phone);

        if ($length <= 7) {
            return str_repeat('*', max(0, $length - 2)) . substr($phone, -2);
        }

        return substr($phone, 0, 4) . str_repeat('*', max(4, $length - 8)) . substr($phone, -4);
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Check if the user is an administrator or executive barangay captain.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('administrator', 'barangay_captain');
    }

    /**
     * Check if the user is an operational member / editor.
     */
    public function isMember(): bool
    {
        return !$this->isAdmin();
    }

    /**
     * Check if the user is an Inventory & Equipment Officer.
     */
    public function isInventoryOfficer(): bool
    {
        return $this->hasRole('inventory_officer', 'bdrrmc_officer');
    }

    /**
     * Check if the user is a Maintenance Officer.
     */
    public function isMaintenanceOfficer(): bool
    {
        return $this->hasRole('maintenance_officer');
    }

    /**
     * Check if the user is a read-only viewer / staff member.
     */
    public function isViewer(): bool
    {
        return $this->hasRole('viewer', 'staff');
    }

    /**
     * Reported assets this user is assigned to.
     */
    public function assignedReportedAssets(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ReportedAsset::class, 'reported_asset_user', 'user_id', 'reported_asset_id')
            ->withTimestamps();
    }

    /**
     * Maintenance work orders this user is assigned to.
     */
    public function assignedMaintenances(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Maintenance::class, 'maintenance_user', 'user_id', 'maintenance_id')
            ->withTimestamps();
    }
}
