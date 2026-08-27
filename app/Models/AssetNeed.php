<?php

namespace App\Models;

use App\Services\AssetNeedStatusService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetNeed extends Model
{
    protected $fillable = [
        'asset_id',
        'asset_type',
        'requirement',
        'unit',
        'maximum_capacity',
        'minimum_level',
        'current_level',
    ];

    protected $casts = [
        'maximum_capacity' => 'decimal:2',
        'minimum_level'    => 'decimal:2',
        'current_level'    => 'decimal:2',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Boot the model — auto-derive status on create and update.
     */
    protected static function booted(): void
    {
        static::creating(function (AssetNeed $need) {
            $need->status = AssetNeedStatusService::deriveStatus(
                (float) $need->current_level,
                (float) $need->minimum_level,
                (float) $need->maximum_capacity
            );
        });

        static::updating(function (AssetNeed $need) {
            $need->status = AssetNeedStatusService::deriveStatus(
                (float) $need->current_level,
                (float) $need->minimum_level,
                (float) $need->maximum_capacity
            );
        });
    }

    /**
     * Get the parent asset.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Replenish stock, capped at maximum_capacity.
     * Triggers model save which re-derives status via the boot hook.
     */
    public function replenish(float $amount): void
    {
        $this->current_level = min(
            (float) $this->current_level + $amount,
            (float) $this->maximum_capacity
        );
        $this->save();
    }

    /**
     * Check if the asset need is in a critical state.
     */
    public function isCritical(): bool
    {
        return in_array($this->status, [
            AssetNeedStatusService::STATUS_REQUIRES_MAINTENANCE,
            AssetNeedStatusService::STATUS_UNDER_MAINTENANCE,
        ]);
    }
}
