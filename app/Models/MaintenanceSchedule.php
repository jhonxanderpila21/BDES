<?php

namespace App\Models;

use App\Services\MaintenanceScheduleService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceSchedule extends Model
{
    use \App\Models\Concerns\HasReceipts;

    protected $fillable = [
        'asset_id',
        'asset_type',
        'maintenance_task',
        'interval_type',
        'interval_days',
        'last_maintenance_date',
    ];

    protected $casts = [
        'last_maintenance_date' => 'date',
        'next_due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::saving(function (MaintenanceSchedule $schedule) {
            $schedule->next_due_date = MaintenanceScheduleService::calculateNextDueDate(
                $schedule->last_maintenance_date,
                $schedule->interval_days
            );

            $schedule->status = MaintenanceScheduleService::deriveStatus(
                $schedule->next_due_date
            );
        });
    }

    /**
     * Get the asset this schedule belongs to.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Refresh the status dynamically against current date.
     * Returns true if status was changed and saved.
     */
    public function refreshStatus(): bool
    {
        $newStatus = MaintenanceScheduleService::deriveStatus($this->next_due_date);
        if ($this->status !== $newStatus) {
            $this->status = $newStatus;
            $this->saveQuietly(); // avoid triggering full saving hooks to prevent updated_at churn
            return true;
        }
        return false;
    }

    /**
     * Mark the maintenance as completed.
     */
    public function markAsCompleted(Carbon $date, ?string $technician = null, ?string $notes = null, ?float $cost = null): Maintenance
    {
        // 1. Update this schedule
        $this->last_maintenance_date = $date->copy();
        $this->save(); // will recalculate next_due_date and status via boot hook

        // 2. Create the historical record in the reactive maintenance table
        return Maintenance::create([
            'asset_id' => $this->asset_id,
            'type' => 'preventive',
            'technician' => $technician,
            'scheduled_date' => $date->format('Y-m-d'),
            'notes' => trim("Completed preventive maintenance: {$this->maintenance_task}\n{$notes}"),
            'status' => 'completed',
            'completed_at' => $date,
            'cost' => $cost > 0 ? $cost : null,
        ]);
    }

    /**
     * Check if the schedule requires attention.
     */
    public function isCritical(): bool
    {
        return in_array($this->status, [
            MaintenanceScheduleService::STATUS_DUE,
            MaintenanceScheduleService::STATUS_OVERDUE,
        ]);
    }
}
