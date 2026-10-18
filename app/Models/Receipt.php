<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * Proof-of-cost evidence (receipt / invoice / quotation) for any costed record.
 */
class Receipt extends Model
{
    public const DISK = 'local';

    protected $fillable = [
        'receiptable_type',
        'receiptable_id',
        'reference_number',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    protected static function booted(): void
    {
        // Remove the physical file once no other receipt row references it
        // (one upload may be linked to several records, e.g. bulk-added assets).
        static::deleting(function (Receipt $receipt) {
            if (! $receipt->file_path) {
                return;
            }
            $shared = static::where('file_path', $receipt->file_path)
                ->where('id', '!=', $receipt->id)
                ->exists();
            if (! $shared && Storage::disk(self::DISK)->exists($receipt->file_path)) {
                Storage::disk(self::DISK)->delete($receipt->file_path);
            }
        });
    }

    public function receiptable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
