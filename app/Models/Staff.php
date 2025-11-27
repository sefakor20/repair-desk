<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StaffRole;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Staff model manages branch-specific staff assignments and roles.
 * This is different from the User model - users are system accounts,
 * staff assignments are branch-specific operational roles.
 */
class Staff extends Model
{
    /** @use HasFactory<\Database\Factories\StaffFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'user_id',
        'branch_id',
        'role',
        'hire_date',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'role' => StaffRole::class,
            'hire_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all permissions for this staff member's role
     */
    public function getPermissions(): array
    {
        return $this->role->permissions();
    }

    /**
     * Check if staff member has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return in_array($permission, $this->getPermissions(), true);
    }

    /**
     * Check if staff member can perform an action
     */
    public function can(string $action): bool
    {
        return $this->hasPermission($action) && $this->is_active && $this->branch->is_active;
    }

    /**
     * Get active staff members only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereHas('branch', fn($q) => $q->where('is_active', true));
    }

    /**
     * Get staff by role
     */
    public function scopeByRole($query, StaffRole $role)
    {
        return $query->where('role', $role->value);
    }

    /**
     * Get staff in a specific branch
     */
    public function scopeInBranch($query, string $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($staff) {
            // Prevent deletion - set inactive instead
            $staff->update(['is_active' => false]);

            return false; // Don't actually delete
        });
    }
}
