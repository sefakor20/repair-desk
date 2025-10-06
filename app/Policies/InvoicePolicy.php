<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     * All authenticated users can view invoices.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * All authenticated users can view invoices.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * Admin, Manager, and Front Desk can create invoices.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager, UserRole::FrontDesk]);
    }

    /**
     * Determine whether the user can update the model.
     * Admin, Manager, and Front Desk can update invoices.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager, UserRole::FrontDesk]);
    }

    /**
     * Determine whether the user can delete the model.
     * Only Admin and Manager can delete invoices.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can restore the model.
     * Only Admin and Manager can restore invoices.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only Admin can permanently delete invoices.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can process payments for invoices.
     * Admin, Manager, and Front Desk can process payments.
     */
    public function processPayment(User $user, Invoice $invoice): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Manager, UserRole::FrontDesk]);
    }
}
