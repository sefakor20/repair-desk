<?php

declare(strict_types=1);

namespace App\Traits;

use App\Services\BranchContextService;

/**
 * Automatically assigns branch_id to models when creating records.
 * Only assigns if branch_id is not already set and user has a branch context.
 */
trait AutoAssignBranch
{
    protected static function bootAutoAssignBranch(): void
    {
        static::creating(function ($model): void {
            // Only auto-assign if branch_id is not already set
            if (empty($model->branch_id)) {
                $branchId = app(BranchContextService::class)->getCurrentBranchId();

                if ($branchId) {
                    $model->branch_id = $branchId;
                }
            }
        });
    }
}
