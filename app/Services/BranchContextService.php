<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Cache;

/**
 * Manages branch context for the current request.
 * Provides centralized access to branch-related data and state.
 */
class BranchContextService
{
    private ?Branch $currentBranch = null;

    private const BRANCH_CACHE_KEY = 'branch_context_%s';
    private const BRANCH_CACHE_TTL = 3600; // 1 hour

    /**
     * Set the current branch context
     */
    public function setCurrentBranch(?Branch $branch): self
    {
        $this->currentBranch = $branch;
        return $this;
    }

    /**
     * Get the current branch context
     */
    public function getCurrentBranch(): ?Branch
    {
        if ($this->currentBranch) {
            return $this->currentBranch;
        }

        if (! auth()->check()) {
            return null;
        }

        $user = auth()->user();

        if (! $user->branch_id) {
            return null;
        }

        // Cache branch to avoid repeated queries
        return Cache::remember(
            sprintf(self::BRANCH_CACHE_KEY, $user->branch_id),
            self::BRANCH_CACHE_TTL,
            fn() => Branch::find($user->branch_id),
        );
    }

    /**
     * Get the current branch ID
     */
    public function getCurrentBranchId(): ?string
    {
        $branch = $this->getCurrentBranch();
        return $branch?->id;
    }

    /**
     * Check if user can access a specific branch
     */
    public function canAccessBranch(Branch|string $branch): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $user = auth()->user();
        $branchId = $branch instanceof Branch ? $branch->id : $branch;

        // Super admin can access any branch
        if ($user->isSuperAdmin()) {
            return true;
        }

        // User can only access their assigned branch
        return $user->branch_id === $branchId;
    }

    /**
     * Get all branches accessible to the current user
     */
    public function getAccessibleBranches()
    {
        if (! auth()->check()) {
            return collect();
        }

        $user = auth()->user();

        // Super admin gets all active branches
        if ($user->isSuperAdmin()) {
            return Branch::where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        // Regular user gets only their branch
        if ($user->branch_id) {
            return Branch::where('id', $user->branch_id)
                ->where('is_active', true)
                ->get();
        }

        return collect();
    }

    /**
     * Verify branch exists and is active
     */
    public function isBranchActive(Branch|string $branch): bool
    {
        if ($branch instanceof Branch) {
            return $branch->is_active;
        }

        return Cache::remember(
            sprintf('branch_active_%s', $branch),
            self::BRANCH_CACHE_TTL,
            fn() => Branch::where('id', $branch)->value('is_active') ?? false,
        );
    }

    /**
     * Clear the branch context cache
     */
    public function clearCache(?string $branchId = null): void
    {
        if ($branchId) {
            Cache::forget(sprintf(self::BRANCH_CACHE_KEY, $branchId));
            Cache::forget(sprintf('branch_active_%s', $branchId));
        } else {
            // Clear all branch caches
            Cache::flush();
        }
    }

    /**
     * Assert the user can access the given branch, throw if not
     */
    public function assertCanAccessBranch(Branch|string $branch): void
    {
        if (! $this->canAccessBranch($branch)) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'You are not authorized to access this branch.',
            );
        }
    }
}
