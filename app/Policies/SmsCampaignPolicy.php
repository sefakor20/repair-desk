<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SmsCampaign;
use App\Models\User;

class SmsCampaignPolicy
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
    public function view(User $user, SmsCampaign $smsCampaign): bool
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
    public function update(User $user, SmsCampaign $smsCampaign): bool
    {
        return $user->hasStaffPermission('manage_settings') && $smsCampaign->status === 'draft';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SmsCampaign $smsCampaign): bool
    {
        return $user->hasStaffPermission('manage_settings') && in_array($smsCampaign->status, ['draft', 'completed', 'cancelled']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SmsCampaign $smsCampaign): bool
    {
        return $user->hasStaffPermission('manage_settings');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SmsCampaign $smsCampaign): bool
    {
        return $user->hasStaffPermission('manage_settings');
    }

    /**
     * Determine whether the user can send the campaign.
     */
    public function send(User $user, SmsCampaign $smsCampaign): bool
    {
        return $user->hasStaffPermission('manage_settings') && in_array($smsCampaign->status, ['draft', 'scheduled']);
    }

    /**
     * Determine whether the user can cancel the campaign.
     */
    public function cancel(User $user, SmsCampaign $smsCampaign): bool
    {
        return $user->hasStaffPermission('manage_settings') && in_array($smsCampaign->status, ['draft', 'scheduled']);
    }
}
