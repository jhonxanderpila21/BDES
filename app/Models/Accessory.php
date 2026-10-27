<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accessory extends Model
{
    use \App\Models\Concerns\HasReceipts;

    protected $fillable = [
        'name',
        'asset_type_id',
        'quantity_in_stock',
        'unit_cost',
        'status',
    ];

    protected $casts = [
        'quantity_in_stock' => 'integer',
        'unit_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the asset type this accessory belongs to
     */
    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }
}
