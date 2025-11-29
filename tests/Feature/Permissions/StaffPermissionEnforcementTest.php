<?php

declare(strict_types=1);

use App\Enums\StaffRole;
use App\Models\{Branch, Staff, User};
use App\Services\StaffPermissionService;

beforeEach(function (): void {
    $this->branch = Branch::factory()->create();
    $this->user = User::factory()->create(['branch_id' => $this->branch->id]);
    $this->permissionService = app(StaffPermissionService::class);
});

describe('StaffPermissionService', function (): void {
    test('super admin has all permissions', function (): void {
        $superAdmin = User::factory()->superAdmin()->create();

        expect($this->permissionService->hasPermission($superAdmin, 'manage_staff'))->toBeTrue()
            ->and($this->permissionService->hasPermission($superAdmin, 'manage_inventory'))->toBeTrue()
            ->and($this->permissionService->hasPermission($superAdmin, 'any_permission'))->toBeTrue()
            ->and($this->permissionService->getPermissions($superAdmin))->toHaveCount(18);
    });

    test('user without branch_id has no permissions', function (): void {
        $user = User::factory()->create(['branch_id' => null]);

        expect($this->permissionService->hasPermission($user, 'manage_staff'))->toBeFalse()
            ->and($this->permissionService->getPermissions($user))->toBeEmpty();
    });

    test('user without staff assignment has no permissions', function (): void {
        expect($this->permissionService->hasPermission($this->user, 'manage_staff'))->toBeFalse()
            ->and($this->permissionService->getPermissions($this->user))->toBeEmpty();
    });

    test('inactive staff has no permissions', function (): void {
        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->branchManager()
            ->inactive()
            ->create();

        expect($this->permissionService->hasPermission($this->user, 'manage_staff'))->toBeFalse()
            ->and($this->permissionService->getPermissions($this->user))->toBeEmpty();
    });

    test('active staff has role-based permissions', function (): void {
        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->branchManager()
            ->create();

        expect($this->permissionService->hasPermission($this->user, 'manage_staff'))->toBeTrue()
            ->and($this->permissionService->hasPermission($this->user, 'manage_tickets'))->toBeTrue()
            ->and($this->permissionService->hasPermission($this->user, 'invalid_permission'))->toBeFalse()
            ->and($this->permissionService->getPermissions($this->user))->toHaveCount(8);
    });

    test('hasAnyPermission returns true if user has any of the permissions', function (): void {
        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->technician()
            ->create();

        expect($this->permissionService->hasAnyPermission($this->user, ['manage_staff', 'view_assigned_tickets']))->toBeTrue()
            ->and($this->permissionService->hasAnyPermission($this->user, ['manage_staff', 'manage_customers']))->toBeFalse();
    });

    test('hasAllPermissions returns true only if user has all permissions', function (): void {
        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->branchManager()
            ->create();

        expect($this->permissionService->hasAllPermissions($this->user, ['manage_staff', 'manage_tickets']))->toBeTrue()
            ->and($this->permissionService->hasAllPermissions($this->user, ['manage_staff', 'create_sales']))->toBeFalse();
    });

    test('getAllPermissions returns all 18 system permissions', function (): void {
        $permissions = $this->permissionService->getAllPermissions();

        expect($permissions)->toHaveCount(18)
            ->toContain('manage_staff', 'manage_tickets', 'manage_inventory', 'manage_customers')
            ->toContain('view_reports', 'manage_settings', 'process_payments')
            ->toContain('view_assigned_tickets', 'update_ticket_status', 'use_inventory', 'create_sales')
            ->toContain('create_tickets', 'schedule_appointments', 'view_inventory')
            ->toContain('create_inventory_adjustments', 'view_sales', 'create_invoices', 'manage_cash_drawer');
    });
});

describe('User Model Permission Helpers', function (): void {
    test('hasStaffPermission delegates to StaffPermissionService', function (): void {
        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->technician()
            ->create();

        expect($this->user->hasStaffPermission('view_assigned_tickets'))->toBeTrue()
            ->and($this->user->hasStaffPermission('manage_staff'))->toBeFalse();
    });

    test('hasAnyStaffPermission checks multiple permissions', function (): void {
        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->cashier()
            ->create();

        expect($this->user->hasAnyStaffPermission(['create_sales', 'manage_staff']))->toBeTrue()
            ->and($this->user->hasAnyStaffPermission(['manage_staff', 'manage_tickets']))->toBeFalse();
    });

    test('hasAllStaffPermissions requires all permissions', function (): void {
        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->inventory()
            ->create();

        expect($this->user->hasAllStaffPermissions(['view_inventory', 'use_inventory']))->toBeTrue()
            ->and($this->user->hasAllStaffPermissions(['view_inventory', 'manage_staff']))->toBeFalse();
    });

    test('activeStaffAssignment returns active staff record', function (): void {
        $staff = Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->receptionist()
            ->create();

        $assignment = $this->user->activeStaffAssignment();

        expect($assignment)->toBeInstanceOf(Staff::class)
            ->and($assignment->id)->toBe($staff->id)
            ->and($assignment->role)->toBe(StaffRole::Receptionist);
    });

    test('activeStaffAssignment returns null for user without assignment', function (): void {
        expect($this->user->activeStaffAssignment())->toBeNull();
    });
});

describe('Middleware Permission Enforcement', function (): void {
    test('middleware blocks unauthenticated users', function (): void {
        $this->get(route('staff.index'))
            ->assertRedirect(route('login'));
    });

    test('middleware blocks users without permission', function (): void {
        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->cashier()
            ->create();

        $this->actingAs($this->user)
            ->get(route('staff.index'))
            ->assertForbidden();
    });

    test('middleware allows users with permission', function (): void {
        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->branchManager()
            ->create();

        $this->actingAs($this->user)
            ->get(route('staff.index'))
            ->assertSuccessful();
    });

    test('middleware allows super admins to access all routes', function (): void {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('staff.index'))
            ->assertSuccessful();
    });
});

describe('Route Permission Enforcement', function (): void {
    test('customer routes require manage_customers permission', function (): void {
        $this->actingAs($this->user)
            ->get(route('customers.index'))
            ->assertForbidden();

        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->branchManager()
            ->create();

        $this->actingAs($this->user)
            ->get(route('customers.index'))
            ->assertSuccessful();
    });

    test('inventory routes require appropriate permissions', function (): void {
        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->inventory()
            ->create();

        // Inventory staff can view
        $this->actingAs($this->user)
            ->get(route('inventory.index'))
            ->assertSuccessful();

        // But cannot create (needs manage_inventory)
        $this->actingAs($this->user)
            ->get(route('inventory.create'))
            ->assertForbidden();
    });

    test('POS routes require sales permissions', function (): void {
        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->cashier()
            ->create();

        $this->actingAs($this->user)
            ->get(route('pos.index'))
            ->assertSuccessful();

        $this->actingAs($this->user)
            ->get(route('pos.create'))
            ->assertSuccessful();
    });

    test('reports require view_reports permission', function (): void {
        $this->actingAs($this->user)
            ->get(route('reports.index'))
            ->assertForbidden();

        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->branchManager()
            ->create();

        $this->actingAs($this->user)
            ->get(route('reports.index'))
            ->assertSuccessful();
    });

    test('settings routes require manage_settings permission', function (): void {
        $this->actingAs($this->user)
            ->get(route('settings.shop'))
            ->assertForbidden();

        Staff::factory()
            ->for($this->user)
            ->for($this->branch)
            ->branchManager()
            ->create();

        $this->actingAs($this->user)
            ->get(route('settings.shop'))
            ->assertSuccessful();
    });
});
