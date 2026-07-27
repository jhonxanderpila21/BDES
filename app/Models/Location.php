<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'type',
        'address',
        'storage_capacity',
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
