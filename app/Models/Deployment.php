<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deployment extends Model
{
    protected $fillable = [
        'asset_id',
        'destination',
        'assigned_team',
        'deployment_date',
        'expected_return_date',
        'purpose',
        'status',
    ];

    protected $casts = [
        'deployment_date' => 'date',
        'expected_return_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the asset being deployed
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
