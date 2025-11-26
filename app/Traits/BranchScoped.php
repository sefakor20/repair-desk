<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Automatically scope queries to the user's assigned branch.
 * Prevents unauthorized access to data from other branches.
 */
class BranchScoped implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Skip scoping if user is not authenticated
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        // Skip scoping for super admins or if user has no branch
        if ($user->isSuperAdmin() || ! $user->branch_id) {
            return;
        }

        // Scope to user's branch
        $builder->where('branch_id', $user->branch_id);
    }

    public function remove(Builder $builder, Model $model): void
    {
        $query = $builder->getQuery();

        foreach ((array) $query->wheres as $key => $where) {
            if ($where['type'] === 'Basic'
                && $where['column'] === 'branch_id'
                && $where['operator'] === '=') {
                unset($query->wheres[$key]);
            }
        }
    }
}
