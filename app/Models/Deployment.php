<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Deployment extends Model
{
    protected $fillable = [
        'asset_id',
        'destination',
        'assigned_team',
        'responsible_person',
        'deployment_date',
        'expected_return_date',
        'purpose',
        'condition_before',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'deployment_date'      => 'date',
        'expected_return_date' => 'date',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];

    /**
     * Get the asset being deployed
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Approving officer user
     */
    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * User alias for approving officer
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the return record associated with this deployment.
     */
    public function returnRecord(): HasOne
    {
        return $this->hasOne(AssetReturn::class, 'deployment_id');
    }

    /**
     * Check if the deployment is returned.
     */
    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    /**
     * Check if the deployment is overdue for return.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'returned'
            && $this->expected_return_date !== null
            && $this->expected_return_date->isPast();
    }
}
