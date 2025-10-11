<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\{PosSale, User};

class PosSalePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PosSale $posSale): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PosSale $posSale): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PosSale $posSale): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can refund the sale.
     */
    public function refund(User $user, PosSale $posSale): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }
}
