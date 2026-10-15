<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Asset extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category_id',
        'type_id',
        'serial_number',
        'manufacturer',
        'purchase_date',
        'acquisition_cost',
        'useful_life_years',
        'salvage_value',
        'salvage_notes',
        'warranty_expiry',
        'location_id',
        'condition',
        'status',
        'notes',
        'retirement_date',
        'retirement_reason',
        'retired_by',
    ];

    protected $casts = [
        'purchase_date'      => 'date',
        'acquisition_cost'   => 'decimal:2',
        'useful_life_years'  => 'integer',
        'salvage_value'      => 'decimal:2',
        'warranty_expiry'    => 'date',
        'retirement_date'    => 'date',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    /**
     * Get the category this asset belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    /**
     * Get the type of this asset
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'type_id');
    }

    /**
     * Get the location of this asset
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Get user who authorized the retirement
     */
    public function retiredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'retired_by');
    }

    /**
     * Get all attachments for this asset
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'asset_id');
    }

    /**
     * Get all digital documents for this asset
     */
    public function documents(): HasMany
    {
        return $this->hasMany(AssetDocument::class, 'asset_id');
    }

    /**
     * Get all deployments of this asset
     */
    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class, 'asset_id');
    }

    /**
     * Get all return records of this asset
     */
    public function returns(): HasMany
    {
        return $this->hasMany(AssetReturn::class, 'asset_id');
    }

    /**
     * Get all maintenance records for this asset
     */
    public function maintenance(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'asset_id');
    }

    /**
     * Get all inspection records for this asset
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'asset_id');
    }

    /**
     * Get all asset need entries for this asset
     */
    public function assetNeeds(): HasMany
    {
        return $this->hasMany(AssetNeed::class, 'asset_id');
    }

    /**
     * Get all preventive maintenance schedules for this asset
     */
    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class, 'asset_id');
    }

    /**
     * Get all accessories for this asset
     */
    public function accessories(): HasMany
    {
        return $this->hasMany(AssetAccessory::class, 'asset_id');
    }

    /**
     * Get all reported asset entries for this asset
     */
    public function reportedAssets(): HasMany
    {
        return $this->hasMany(ReportedAsset::class, 'asset_id');
    }

    /**
     * Get all audit trail events for this asset
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->orderByDesc('created_at');
    }

    /**
     * Scope for active (non-retired) assets
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'retired');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeDeployed($query)
    {
        return $query->where('status', 'deployed');
    }

    public function scopeUnderMaintenance($query)
    {
        return $query->where('status', 'under_maintenance');
    }

    public function scopeOutOfService($query)
    {
        return $query->where('status', 'out_of_service');
    }

    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }

    public function scopeRetired($query)
    {
        return $query->where('status', 'retired');
    }

    /**
     * Check if this asset has any open (unresolved) reports.
     */
    public function getHasOpenReportAttribute(): bool
    {
        return $this->reportedAssets()
            ->whereIn('status', \App\Services\ReportedAssetService::openStatuses())
            ->exists();
    }

    /**
     * Financial & Valuation Accessors (Philippine Government Standard: COA Circular No. 2022-004 & GAM for LGUs)
     */
    public function getIsCapitalizedPpeAttribute(): bool
    {
        return \App\Services\PhilippineAccountingStandardService::isCapitalizedPpe(
            $this->acquisition_cost !== null ? (float) $this->acquisition_cost : null
        );
    }

    public function getIsSemiExpendableAttribute(): bool
    {
        return \App\Services\PhilippineAccountingStandardService::isSemiExpendable(
            $this->acquisition_cost !== null ? (float) $this->acquisition_cost : null
        );
    }

    public function getCoaClassificationAttribute(): string
    {
        return \App\Services\PhilippineAccountingStandardService::getClassification(
            $this->acquisition_cost !== null ? (float) $this->acquisition_cost : null
        );
    }

    public function getEffectiveUsefulLifeAttribute(): int
    {
        return $this->useful_life_years > 0 ? $this->useful_life_years : 5;
    }

    public function getCalculatedSalvageValueAttribute(): float
    {
        // Enforce Philippine Statutory Limit: Items below ₱50,000 (Semi-Expendable) cannot have salvage value
        if (!$this->is_capitalized_ppe) {
            return 0.0;
        }

        if ($this->salvage_value !== null && $this->salvage_value > 0) {
            return (float) $this->salvage_value;
        }

        // Philippine COA standard default: 10% residual value of acquisition cost
        $cost = (float) ($this->acquisition_cost ?? 0);
        return round($cost * \App\Services\PhilippineAccountingStandardService::DEFAULT_RESIDUAL_RATE, 2);
    }

    public function getDepreciableAmountAttribute(): float
    {
        // Items below ₱50,000 cannot be depreciated under COA standard
        if (!$this->is_capitalized_ppe) {
            return 0.0;
        }

        $cost = (float) ($this->acquisition_cost ?? 0);
        return max(0.0, round($cost - $this->calculated_salvage_value, 2));
    }

    public function getAnnualDepreciationAttribute(): float
    {
        if (!$this->is_capitalized_ppe) {
            return 0.0;
        }

        $life = $this->effective_useful_life;
        return $life > 0 ? round($this->depreciable_amount / $life, 2) : 0.0;
    }

    public function getMonthlyDepreciationAttribute(): float
    {
        if (!$this->is_capitalized_ppe) {
            return 0.0;
        }

        return round($this->annual_depreciation / 12, 2);
    }

    public function getAgeInYearsAttribute(): float
    {
        if (!$this->purchase_date) {
            return 0.0;
        }

        $years = \Carbon\Carbon::parse($this->purchase_date)->floatDiffInYears(now());
        return max(0.0, round($years, 2));
    }

    public function getAccumulatedDepreciationAttribute(): float
    {
        if (!$this->is_capitalized_ppe) {
            return 0.0;
        }

        $cappedAge = min((float) $this->effective_useful_life, $this->age_in_years);
        $accum = $cappedAge * $this->annual_depreciation;
        return min($this->depreciable_amount, round($accum, 2));
    }

    public function getCurrentBookValueAttribute(): float
    {
        $cost = (float) ($this->acquisition_cost ?? 0);

        if (!$this->is_capitalized_ppe) {
            return $cost;
        }

        $val = $cost - $this->accumulated_depreciation;
        return max($this->calculated_salvage_value, round($val, 2));
    }

    public function getDepreciationPercentageAttribute(): float
    {
        if (!$this->is_capitalized_ppe) {
            return 0.0;
        }

        $cost = (float) ($this->acquisition_cost ?? 0);
        if ($cost <= 0) {
            return 0.0;
        }
        return min(100.0, round(($this->accumulated_depreciation / $cost) * 100, 1));
    }
}
