<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportedAssetStatusHistory extends Model
{
    protected $fillable = [
        'reported_asset_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the reported asset this history entry belongs to.
     */
    public function reportedAsset(): BelongsTo
    {
        return $this->belongsTo(ReportedAsset::class, 'reported_asset_id');
    }

    /**
     * Get the user who made this status change.
     */
    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
