<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssetDocument extends Model
{
    protected $fillable = [
        'asset_id',
        'title',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'expiry_date',
        'uploaded_by',
        'notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Asset the document belongs to.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Uploader user.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Check if the document has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if the document is expiring soon (within 30 days).
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date && !$this->is_expired && $this->expiry_date->diffInDays(now()) <= 30;
    }
}

