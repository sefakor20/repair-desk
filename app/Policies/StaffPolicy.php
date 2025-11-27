<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\StaffRole;
use App\Models\Staff;
use App\Models\User;

class StaffPolicy
{
    /**
     * Determine whether the user can view any staff members.
     */
    public function viewAny(User $user): bool
    {
        // Only super admins and branch managers can view staff
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Branch staff can view other staff in same branch if they're a manager
        $branchStaff = $user->staffAssignments()
            ->where('branch_id', $user->branch_id)
            ->first();

        return $branchStaff?->role === StaffRole::BranchManager;
    }

    /**
     * Determine whether the user can view the staff member.
     */
    public function view(User $user, Staff $staff): bool
    {
        // Super admin can view any staff
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Can only view staff from their own branch
        if ($user->branch_id !== $staff->branch_id) {
            return false;
        }

        // Branch managers and the staff member themselves can view
        $branchStaff = $user->staffAssignments()
            ->where('branch_id', $user->branch_id)
            ->first();

        return $branchStaff?->role === StaffRole::BranchManager || $staff->user_id === $user->id;
    }

    /**
     * Determine whether the user can create staff.
     */
    public function create(User $user): bool
    {
        // Only super admins and branch managers can create staff
        if ($user->isSuperAdmin()) {
            return true;
        }

        $branchStaff = $user->staffAssignments()
            ->where('branch_id', $user->branch_id)
            ->first();

        return $branchStaff?->role === StaffRole::BranchManager;
    }

    /**
     * Determine whether the user can update the staff member.
     */
    public function update(User $user, Staff $staff): bool
    {
        // Super admin can update any staff
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Can only update staff from their own branch
        if ($user->branch_id !== $staff->branch_id) {
            return false;
        }

        // Branch managers can update their staff
        $branchStaff = $user->staffAssignments()
            ->where('branch_id', $user->branch_id)
            ->first();

        return $branchStaff?->role === StaffRole::BranchManager;
    }

    /**
     * Determine whether the user can delete the staff member.
     */
    public function delete(User $user, Staff $staff): bool
    {
        // Super admin can delete any staff
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Can only delete staff from their own branch
        if ($user->branch_id !== $staff->branch_id) {
            return false;
        }

        // Branch managers can delete their staff (soft delete/deactivate)
        $branchStaff = $user->staffAssignments()
            ->where('branch_id', $user->branch_id)
            ->first();

        return $branchStaff?->role === StaffRole::BranchManager;
    }

    /**
     * Determine whether the user can restore the staff member.
     */
    public function restore(User $user, Staff $staff): bool
    {
        // Same as delete since we use soft deactivation
        return $this->delete($user, $staff);
    }

    /**
     * Determine whether the user can permanently delete the staff member.
     */
    public function forceDelete(User $user, Staff $staff): bool
    {
        // Only super admin can permanently delete
        return $user->isSuperAdmin();
    }
}
