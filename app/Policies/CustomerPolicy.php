<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any models.
     * All authenticated users can view customers.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * All authenticated users can view customers.
     */
    public function view(User $user, Customer $customer): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * All authenticated users can create customers.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * All authenticated users can update customers.
     */
    public function update(User $user, Customer $customer): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     * Only Admin and Manager can delete customers.
     */
    public function delete(User $user, Customer $customer): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can restore the model.
     * Only Admin and Manager can restore customers.
     */
    public function restore(User $user, Customer $customer): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only Admin can permanently delete customers.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->role === UserRole::Admin;
    }
}
