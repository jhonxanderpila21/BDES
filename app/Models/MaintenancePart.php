<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenancePart extends Model
{
    protected $fillable = [
        'maintenance_id',
        'inventory_type',
        'inventory_id',
        'part_name',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'unit_cost'  => 'decimal:2',
        'total_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (MaintenancePart $part) {
            if (empty($part->total_cost)) {
                $part->total_cost = round((float) $part->quantity * (float) $part->unit_cost, 2);
            }
        });
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class, 'maintenance_id');
    }

    /**
     * Resolve the source inventory item if linked
     */
    public function getInventoryItemAttribute()
    {
        if ($this->inventory_type === 'attachment' && $this->inventory_id) {
            return Attachment::find($this->inventory_id);
        }

        if ($this->inventory_type === 'accessory' && $this->inventory_id) {
            return AssetAccessory::find($this->inventory_id);
        }

        return null;
    }
}

