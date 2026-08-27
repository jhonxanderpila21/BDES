<?php

namespace App\Models;

use App\Services\ReportedAssetService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportedAsset extends Model
{
    protected $fillable = [
        'asset_id',
        'asset_category',
        'asset_name',
        'reported_by',
        'reported_type',
        'status',
        'location',
        'description',
        'action',
        'reported_date',
        'resolved_date',
    ];

    protected $casts = [
        'reported_date' => 'date',
        'resolved_date' => 'date',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::saving(function (ReportedAsset $report) {
            // Auto-sync denormalized asset fields when asset_id changes
            if ($report->isDirty('asset_id') && $report->asset_id) {
                $asset = Asset::with('category')->find($report->asset_id);
                if ($asset) {
                    $report->asset_name = $asset->code . ' — ' . $asset->name;
                    $report->asset_category = $asset->category->name ?? 'Unknown';
                }
            }

            // Auto-set resolved_date when status transitions to Done or Closed
            if ($report->isDirty('status') && in_array($report->status, [
                ReportedAssetService::STATUS_DONE,
                ReportedAssetService::STATUS_CLOSED,
            ]) && !$report->resolved_date) {
                $report->resolved_date = Carbon::today();
            }
        });
    }

    // ─── Relationships ───

    /**
     * Get the asset this report is about.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Get the user who filed this report.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the status history entries for this report.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(ReportedAssetStatusHistory::class, 'reported_asset_id');
    }

    // ─── Status Transition ───

    /**
     * Attempt to change the report's status through the governed workflow.
     *
     * @return bool True if the transition was applied, false if it was invalid.
     */
    public function changeStatus(string $newStatus, ?int $userId = null, ?string $notes = null): bool
    {
        $currentStatus = $this->status;

        if (!ReportedAssetService::isValidTransition($currentStatus, $newStatus)) {
            return false;
        }

        $this->status = $newStatus;
        $this->save();

        // Log the transition
        $this->statusHistories()->create([
            'from_status' => $currentStatus,
            'to_status'   => $newStatus,
            'changed_by'  => $userId,
            'notes'       => $notes,
        ]);

        return true;
    }

    /**
     * Check if this report is in an "open" (active/unresolved) state.
     */
    public function isOpen(): bool
    {
        return in_array($this->status, ReportedAssetService::openStatuses(), true);
    }
}
