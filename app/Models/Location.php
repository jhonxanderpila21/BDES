<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use \App\Traits\Archivable;

    protected $fillable = [
        'name',
        'type',
        'address',
        'storage_capacity',
        'deleted_by',
        'deletion_reason',
    ];

    protected $casts = [
        'storage_capacity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all assets at this location
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'location_id');
    }
}
