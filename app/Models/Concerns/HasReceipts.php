<?php

namespace App\Models\Concerns;

use App\Models\Receipt;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Add to any model that carries a cost so it can hold proof-of-cost files.
 */
trait HasReceipts
{
    public static function bootHasReceipts(): void
    {
        // Hard deletes clean up receipts; soft deletes keep evidence for audit.
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }
            $model->receipts()->get()->each->delete();
        });
    }

    public function receipts(): MorphMany
    {
        return $this->morphMany(Receipt::class, 'receiptable')->latest();
    }
}

