<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Shift $shift): bool
    {
        return true;
    }

    public function open(User $user): bool
    {
        // Check if user already has an open shift
        $hasOpenShift = Shift::where('opened_by', $user->id)
            ->where('status', 'open')
            ->exists();

        return ! $hasOpenShift;
    }

    public function close(User $user, Shift $shift): bool
    {
        // Can only close if shift is open and belongs to the user
        return $shift->isOpen() && $shift->opened_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->open($user);
    }

    public function update(User $user, Shift $shift): bool
    {
        return $shift->isOpen() && $shift->opened_by === $user->id;
    }

    public function delete(User $user, Shift $shift): bool
    {
        return false;
    }
}
