<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class StaffPermissionService
{
    /**
     * Check if a user has a specific staff permission
     */
    public function hasPermission(User $user, string $permission): bool
    {
        // Super admins (Admin role without branch) have all permissions
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Regular admins (Admin role with branch) also have all permissions
        if ($user->role === \App\Enums\UserRole::Admin) {
            return true;
        }

        // Get active staff assignment for user's current branch
        $staff = $this->getActiveStaffAssignment($user);

        if (! $staff) {
            return false;
        }

        return $staff->hasPermission($permission);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($user, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermission($user, $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for a user
     */
    public function getPermissions(User $user): array
    {
        // Super admins and regular admins have all permissions
        if ($user->isSuperAdmin() || $user->role === \App\Enums\UserRole::Admin) {
            return $this->getAllPermissions();
        }

        $staff = $this->getActiveStaffAssignment($user);

        if (! $staff) {
            return [];
        }

        return $staff->getPermissions();
    }

    /**
     * Get the active staff assignment for a user
     */
    public function getActiveStaffAssignment(User $user): ?Staff
    {
        if (! $user->branch_id) {
            return null;
        }

        return Cache::remember(
            "staff_assignment_{$user->id}_{$user->branch_id}",
            now()->addMinutes(10),
            fn() => Staff::query()
                ->where('user_id', $user->id)
                ->where('branch_id', $user->branch_id)
                ->where('is_active', true)
                ->first(),
        );
    }

    /**
     * Clear cached staff assignment
     */
    public function clearCache(User $user): void
    {
        if ($user->branch_id) {
            Cache::forget("staff_assignment_{$user->id}_{$user->branch_id}");
        }
    }

    /**
     * Get all available permissions in the system
     */
    public function getAllPermissions(): array
    {
        return [
            'manage_staff',
            'manage_tickets',
            'manage_inventory',
            'manage_customers',
            'view_reports',
            'manage_settings',
            'process_payments',
            'view_assigned_tickets',
            'update_ticket_status',
            'use_inventory',
            'create_sales',
            'create_tickets',
            'schedule_appointments',
            'view_inventory',
            'create_inventory_adjustments',
            'view_sales',
            'create_invoices',
            'manage_cash_drawer',
        ];
    }
}
