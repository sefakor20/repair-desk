<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DeviceModel;
use App\Models\User;

class DeviceModelPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasStaffPermission('manage_settings');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DeviceModel $deviceModel): bool
    {
        return $user->hasStaffPermission('manage_settings');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasStaffPermission('manage_settings');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DeviceModel $deviceModel): bool
    {
        return $user->hasStaffPermission('manage_settings');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DeviceModel $deviceModel): bool
    {
        return $user->hasStaffPermission('manage_settings');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DeviceModel $deviceModel): bool
    {
        return $user->hasStaffPermission('manage_settings');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DeviceModel $deviceModel): bool
    {
        return $user->hasStaffPermission('manage_settings');
    }
}
