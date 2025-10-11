<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Device;
use App\Models\User;

class DevicePolicy
{
    /**
     * Determine whether the user can view any models.
     * All authenticated users can view devices.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * All authenticated users can view devices.
     */
    public function view(User $user, Device $device): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * All authenticated users can create devices.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * All authenticated users can update devices.
     */
    public function update(User $user, Device $device): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     * Only Admin and Manager can delete devices.
     */
    public function delete(User $user, Device $device): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can restore the model.
     * Only Admin and Manager can restore devices.
     */
    public function restore(User $user, Device $device): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only Admin can permanently delete devices.
     */
    public function forceDelete(User $user, Device $device): bool
    {
        return $user->role === UserRole::Admin;
    }
}
