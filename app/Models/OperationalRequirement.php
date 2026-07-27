<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalRequirement extends Model
{
    protected $table = 'operational_requirements';

    protected $fillable = [
        'asset_type_id',
        'requirement_type',
        'unit',
        'current_level',
        'maximum_capacity',
        'minimum_level',
        'description',
    ];

    protected $casts = [
        'current_level' => 'decimal:2',
        'maximum_capacity' => 'decimal:2',
        'minimum_level' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the asset type this requirement applies to
     */
    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }

    public function isLowStock(): bool
    {
        return $this->current_level <= $this->minimum_level;
    }

    /**
     * Add stock, capped at the maximum capacity.
     */
    public function refill(float $amount): void
    {
        $this->current_level = min($this->current_level + $amount, $this->maximum_capacity);
        $this->save();
    }

    /**
     * Use up stock, floored at zero.
     */
    public function consume(float $amount): void
    {
        $this->current_level = max($this->current_level - $amount, 0);
        $this->save();
    }
}
