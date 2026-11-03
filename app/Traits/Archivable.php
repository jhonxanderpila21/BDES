<?php

namespace App\Traits;

use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait Archivable
{
    use SoftDeletes;

    /**
     * Boot the Archivable trait for Eloquent models.
     */
    public static function bootArchivable(): void
    {
        static::deleting(function ($model) {
            if (!$model->isForceDeleting()) {
                $hasDeletedByCol = Schema::hasColumn($model->getTable(), 'deleted_by');
                $hasReasonCol = Schema::hasColumn($model->getTable(), 'deletion_reason');

                if ($hasDeletedByCol && empty($model->deleted_by) && Auth::check()) {
                    $model->deleted_by = Auth::id();
                }

                if ($hasReasonCol && empty($model->deletion_reason)) {
                    $model->deletion_reason = request()->input('deletion_reason')
                        ?? request()->input('reason')
                        ?? 'Archived via system management interface.';
                }

                if ($model->isDirty(['deleted_by', 'deletion_reason'])) {
                    $model->saveQuietly();
                }

                $identifier = $model->code ?? $model->work_order_number ?? $model->ticket_number ?? $model->name ?? "#{$model->getKey()}";
                $entityName = class_basename($model);

                AuditTrailService::log(
                    'archived',
                    $model,
                    "{$entityName} [{$identifier}] archived. Reason: " . ($model->deletion_reason ?? 'None provided.'),
                    $model->toArray(),
                    [
                        'deleted_at'      => now(),
                        'deleted_by'      => $model->deleted_by,
                        'deletion_reason' => $model->deletion_reason,
                    ],
                    $model->deleted_by ?? Auth::id()
                );
            }
        });

        static::restoring(function ($model) {
            $identifier = $model->code ?? $model->work_order_number ?? $model->ticket_number ?? $model->name ?? "#{$model->getKey()}";
            $entityName = class_basename($model);

            AuditTrailService::log(
                'restored',
                $model,
                "{$entityName} [{$identifier}] restored from archives to active module.",
                [
                    'deleted_at'      => $model->deleted_at,
                    'deleted_by'      => $model->deleted_by,
                    'deletion_reason' => $model->deletion_reason,
                ],
                [
                    'deleted_at'      => null,
                    'deleted_by'      => null,
                    'deletion_reason' => null,
                ],
                Auth::id()
            );

            if (Schema::hasColumn($model->getTable(), 'deleted_by')) {
                $model->deleted_by = null;
            }
            if (Schema::hasColumn($model->getTable(), 'deletion_reason')) {
                $model->deletion_reason = null;
            }
        });
    }

    /**
     * User who deleted/archived the record.
     */
    public function deletedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

