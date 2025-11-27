<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SmsDeliveryLog;
use App\Models\User;

class SmsDeliveryLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only admins and managers can view SMS logs
        return $user->hasAnyStaffPermission(['manage_settings', 'view_reports']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SmsDeliveryLog $smsDeliveryLog): bool
    {
        return $user->hasAnyStaffPermission(['manage_settings', 'view_reports']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SmsDeliveryLog $smsDeliveryLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SmsDeliveryLog $smsDeliveryLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SmsDeliveryLog $smsDeliveryLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SmsDeliveryLog $smsDeliveryLog): bool
    {
        return false;
    }
}
