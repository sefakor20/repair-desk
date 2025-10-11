<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CashDrawerSession;
use App\Models\User;

class CashDrawerSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CashDrawerSession $cashDrawerSession): bool
    {
        return true;
    }

    public function open(User $user): bool
    {
        // Check if there's already an open session
        $hasOpenSession = CashDrawerSession::where('status', 'open')->exists();

        return ! $hasOpenSession;
    }

    public function close(User $user, CashDrawerSession $cashDrawerSession): bool
    {
        // Can only close if session is open
        return $cashDrawerSession->isOpen();
    }

    public function create(User $user): bool
    {
        return $this->open($user);
    }

    public function update(User $user, CashDrawerSession $cashDrawerSession): bool
    {
        return $cashDrawerSession->isOpen();
    }

    public function delete(User $user, CashDrawerSession $cashDrawerSession): bool
    {
        return false;
    }
}
