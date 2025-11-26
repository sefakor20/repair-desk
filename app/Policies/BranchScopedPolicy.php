<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Auth\Access\Response;

/**
 * Base policy with common authorization checks for branch-scoped resources
 */
class BranchScopedPolicy
{
    /**
     * Check if user can view branch-scoped resources
     */
    protected function canViewBranch(User $user, ?Branch $branch): Response
    {
        if (! $branch) {
            return Response::allow();
        }

        if ($user->isSuperAdmin()) {
            return Response::allow();
        }

        return $user->branch_id === $branch->id
            ? Response::allow()
            : Response::deny('You cannot access data from other branches.');
    }

    /**
     * Check if user can create branch-scoped resources
     */
    protected function canCreateInBranch(User $user, ?Branch $branch): Response
    {
        if (! $branch) {
            return Response::allow();
        }

        if (! in_array($user->role->value, ['admin', 'manager'], true)) {
            return Response::deny('Only administrators and managers can create resources.');
        }

        if ($user->isSuperAdmin()) {
            return Response::allow();
        }

        return $user->branch_id === $branch->id
            ? Response::allow()
            : Response::deny('You can only create resources in your assigned branch.');
    }

    /**
     * Check if user can update branch-scoped resources
     */
    protected function canUpdateInBranch(User $user, ?Branch $branch): Response
    {
        if (! $branch) {
            return Response::allow();
        }

        if (! in_array($user->role->value, ['admin', 'manager'], true)) {
            return Response::deny('Only administrators and managers can update resources.');
        }

        if ($user->isSuperAdmin()) {
            return Response::allow();
        }

        return $user->branch_id === $branch->id
            ? Response::allow()
            : Response::deny('You can only update resources in your assigned branch.');
    }

    /**
     * Check if user can delete branch-scoped resources
     */
    protected function canDeleteInBranch(User $user, ?Branch $branch): Response
    {
        if (! $branch) {
            return Response::allow();
        }

        if ($user->role->value !== 'admin') {
            return Response::deny('Only administrators can delete resources.');
        }

        if ($user->isSuperAdmin()) {
            return Response::allow();
        }

        return $user->branch_id === $branch->id
            ? Response::allow()
            : Response::deny('You can only delete resources in your assigned branch.');
    }
}
