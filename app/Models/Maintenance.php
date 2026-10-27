<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Maintenance extends Model
{
    use \App\Models\Concerns\HasReceipts;

    protected $fillable = [
        'work_order_number',
        'asset_id',
        'type',
        'priority',
        'technician',
        'assigned_to',
        'scheduled_date',
        'estimated_duration',
        'cost',
        'parts_used',
        'notes',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_at'   => 'datetime',
        'cost'           => 'decimal:2',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Maintenance $maintenance) {
            if (empty($maintenance->work_order_number)) {
                $count = static::whereYear('created_at', now()->year)->count() + 1;
                $maintenance->work_order_number = 'WO-' . now()->year . '-' . Str::padLeft($count, 4, '0');
            }
        });
    }

    /**
     * Get the asset being maintained
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Assigned staff/officer user
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
