<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Maintenance extends Model
{
    use \App\Models\Concerns\HasReceipts, \App\Traits\Archivable;

    protected $fillable = [
        'work_order_number',
        'asset_id',
        'inspection_id',
        'type',
        'priority',
        'technician',
        'assigned_to',
        'scheduled_date',
        'started_at',
        'estimated_duration',
        'cost',
        'parts_used',
        'notes',
        'status',
        'completed_at',
        'final_condition',
        'verified_by',
        'verification_notes',
        'confirmed_at',
        'confirmed_by',
        'deleted_by',
        'deletion_reason',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
        'confirmed_at'   => 'datetime',
        'cost'           => 'decimal:2',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Maintenance $maintenance) {
            if (empty($maintenance->work_order_number)) {
                $count = static::whereYear('created_at', now()->year)->count() + 1;
                $maintenance->work_order_number = 'WO-' . now()->year . '-' . Str::padLeft($count, 4, '0');
            }
        });
    }

    /**
     * Get the asset being maintained
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Primary assigned staff/officer user (backward compatibility)
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Assigned personnel working on this maintenance work order (multiple)
     */
    public function assignedPersonnel(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'maintenance_user', 'maintenance_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * User who confirmed this maintenance work order
     */
    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * User who verified the operational status / completed maintenance
     */
    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Inspection that initiated this work order (if any)
     */
    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id');
    }

    /**
     * Parts and materials consumed during maintenance
     */
    public function parts(): HasMany
    {
        return $this->hasMany(MaintenancePart::class, 'maintenance_id');
    }

    /**
     * Check if the work order is confirmed
     */
    public function isConfirmed(): bool
    {
        return !is_null($this->confirmed_at);
    }
}
