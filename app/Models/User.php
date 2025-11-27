<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasUlids;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'active',
        'branch_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'active' => 'boolean',
        ];
    }

    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function ticketNotes(): HasMany
    {
        return $this->hasMany(TicketNote::class);
    }

    public function processedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }

    public function inventoryAdjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class, 'adjusted_by');
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function staffAssignments(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::Admin && ! $this->branch_id;
    }

    /**
     * Check if user can manage the given branch
     */
    public function canManageBranch(Branch|string $branch): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $branchId = $branch instanceof Branch ? $branch->id : $branch;
        return $this->branch_id === $branchId;
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Check if user has a specific staff permission
     */
    public function hasStaffPermission(string $permission): bool
    {
        return app(\App\Services\StaffPermissionService::class)
            ->hasPermission($this, $permission);
    }

    /**
     * Check if user has any of the given staff permissions
     */
    public function hasAnyStaffPermission(array $permissions): bool
    {
        return app(\App\Services\StaffPermissionService::class)
            ->hasAnyPermission($this, $permissions);
    }

    /**
     * Check if user has all of the given staff permissions
     */
    public function hasAllStaffPermissions(array $permissions): bool
    {
        return app(\App\Services\StaffPermissionService::class)
            ->hasAllPermissions($this, $permissions);
    }

    /**
     * Get the active staff assignment for this user
     */
    public function activeStaffAssignment(): ?Staff
    {
        return app(\App\Services\StaffPermissionService::class)
            ->getActiveStaffAssignment($this);
    }
}
