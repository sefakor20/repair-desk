<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\InventoryItem;
use App\Models\User;

class InventoryItemPolicy
{
    /**
     * Determine whether the user can view any models.
     * All authenticated users can view inventory.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * All authenticated users can view inventory items.
     */
    public function view(User $user, InventoryItem $inventoryItem): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * Only Admin and Manager can manage inventory.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can update the model.
     * Only Admin and Manager can manage inventory.
     */
    public function update(User $user, InventoryItem $inventoryItem): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can delete the model.
     * Only Admin and Manager can delete inventory items.
     */
    public function delete(User $user, InventoryItem $inventoryItem): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can restore the model.
     * Only Admin and Manager can restore inventory items.
     */
    public function restore(User $user, InventoryItem $inventoryItem): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only Admin can permanently delete inventory items.
     */
    public function forceDelete(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can adjust inventory quantities.
     * Only Admin and Manager can adjust inventory.
     */
    public function adjustQuantity(User $user, InventoryItem $inventoryItem): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }
}
