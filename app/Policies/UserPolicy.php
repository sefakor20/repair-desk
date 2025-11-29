<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * Only Admin can view all users.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can view the model.
     * Admin can view any user, others can only view themselves.
     */
    public function view(User $user, User $model): bool
    {
        return $user->role === UserRole::Admin || $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     * Only Admin can create users.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can update the model.
     * Admin can update any user, others can only update themselves.
     */
    public function update(User $user, User $model): bool
    {
        return $user->role === UserRole::Admin || $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     * Only Admin can delete users, and cannot delete themselves.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->role === UserRole::Admin && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     * Only Admin can restore users.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only Admin can permanently delete users.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->role === UserRole::Admin && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can view reports.
     * Admin, Manager, or staff with view_reports permission can view reports.
     */
    public function viewReports(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager])
            || $user->hasStaffPermission('view_reports');
    }

    /**
     * Determine whether the user can access system settings.
     * Admin or staff with manage_settings permission can access settings.
     */
    public function accessSettings(User $user): bool
    {
        return $user->role === UserRole::Admin
            || $user->hasStaffPermission('manage_settings');
    }
}
