<?php

namespace App\Models;

use App\Services\AssetAccessoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAccessory extends Model
{
    use \App\Models\Concerns\HasReceipts;

    protected $fillable = [
        'asset_id',
        'asset_type',
        'accessory_name',
        'quantity',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['total_value'];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::saving(function (AssetAccessory $accessory) {
            $accessory->status = AssetAccessoryService::deriveStatus($accessory->quantity);
        });
    }

    /**
     * Get the asset this accessory belongs to.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Get the computed total value of this accessory inventory.
     */
    public function getTotalValueAttribute(): float
    {
        return round($this->quantity * (float) $this->unit_cost, 2);
    }
}
