<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetReturn extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'deployment_id',
        'asset_id',
        'returned_by',
        'received_by',
        'return_date',
        'condition',
        'damage_notes',
        'missing_accessories',
        'actual_location',
        'status',
    ];

    protected $casts = [
        'return_date' => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Get the asset that was returned
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Receiving officer user
     */
    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the deployment associated with this return
     */
    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class, 'deployment_id');
    }
}
