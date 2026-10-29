<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalRequirement extends Model
{
    use \App\Models\Concerns\HasReceipts;

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

    protected $appends = ['using_assets'];

    /**
     * Get the asset type this requirement applies to
     */
    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }

    /**
     * Get all assets using or requiring this operational supply
     */
    public function getUsingAssetsAttribute()
    {
        $assetsFromType = collect();
        if ($this->relationLoaded('assetType') && $this->assetType) {
            $assetsFromType = $this->assetType->relationLoaded('assets')
                ? $this->assetType->assets
                : $this->assetType->assets()->with('location:id,name')->get();
        } elseif ($this->asset_type_id) {
            $assetsFromType = Asset::where('type_id', $this->asset_type_id)->with('location:id,name')->get();
        }

        $assetsFromNeeds = AssetNeed::where('requirement', 'like', '%' . $this->requirement_type . '%')
            ->with(['asset.location:id,name'])
            ->get()
            ->pluck('asset')
            ->filter();

        return $assetsFromType->concat($assetsFromNeeds)->unique('id')->values();
    }

    public function restocks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OperationalRequirementRestock::class, 'operational_requirement_id')->latest();
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
