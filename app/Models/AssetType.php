<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetType extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'serialized',
        'borrowable',
        'requires_maintenance',
        'requires_inspection',
        'has_accessories',
        'has_attachments',
        'requires_operational_requirements',
    ];

    protected $casts = [
        'serialized' => 'boolean',
        'borrowable' => 'boolean',
        'requires_maintenance' => 'boolean',
        'requires_inspection' => 'boolean',
        'has_accessories' => 'boolean',
        'has_attachments' => 'boolean',
        'requires_operational_requirements' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the category this type belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    /**
     * Get all assets of this type
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'type_id');
    }
}
