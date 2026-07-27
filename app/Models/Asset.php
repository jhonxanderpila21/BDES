<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'warranty_expiry',
        'location_id',
        'condition',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * Get all attachments for this asset
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'asset_id');
    }

    /**
     * Get all deployments of this asset
     */
    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class, 'asset_id');
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
}
