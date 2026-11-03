<?php

namespace App\Models;

use App\Models\Concerns\HasReceipts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalRequirementRestock extends Model
{
    use HasReceipts;

    protected $fillable = [
        'operational_requirement_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'restocked_at',
        'supplier',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'restocked_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(OperationalRequirement::class, 'operational_requirement_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

