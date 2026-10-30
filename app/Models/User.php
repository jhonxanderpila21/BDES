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
    use HasFactory, Notifiable, SoftDeletes;

    public const DEFAULT_TEMP_PASSWORD = 'TempPass_123';

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
        'password',
        'role',
        'is_active',
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
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
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
}
