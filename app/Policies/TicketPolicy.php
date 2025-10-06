<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     * All authenticated users can view tickets.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * All authenticated users can view tickets.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * All authenticated users can create tickets.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * All authenticated users can update tickets.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     * Only Admin and Manager can delete tickets.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can restore the model.
     * Only Admin and Manager can restore tickets.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only Admin can permanently delete tickets.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can assign the ticket to a technician.
     * Admin, Manager, and Front Desk can assign tickets.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager, UserRole::FrontDesk]);
    }
}
