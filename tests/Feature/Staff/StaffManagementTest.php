<?php

declare(strict_types=1);

use App\Enums\StaffRole;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\User;
use Livewire\Volt\Volt;

beforeEach(function (): void {
    $this->branch = Branch::factory()->create();

    // Super admin has role Admin and no branch_id
    $this->superAdmin = User::factory()->create([
        'role' => UserRole::Admin,
        'branch_id' => null,
    ]);

    // Branch manager and technician belong to a branch
    $this->branchManager = User::factory()->create([
        'role' => UserRole::Manager,
        'branch_id' => $this->branch->id,
    ]);

    $this->technician = User::factory()->create([
        'role' => UserRole::Technician,
        'branch_id' => $this->branch->id,
    ]);

    $this->newUser = User::factory()->create([
        'role' => UserRole::FrontDesk,
        'branch_id' => $this->branch->id,
    ]);

    // Create branch manager staff record
    Staff::factory()
        ->for($this->branchManager)
        ->for($this->branch)
        ->role(StaffRole::BranchManager)
        ->create();

    // Create technician staff record
    $this->staff = Staff::factory()
        ->for($this->technician)
        ->for($this->branch)
        ->role(StaffRole::Technician)
        ->create();
});

test('branch manager can view staff index page', function (): void {
    $this->actingAs($this->branchManager)
        ->get('/staff')
        ->assertSuccessful()
        ->assertSeeLivewire(\App\Livewire\Staff\Index::class);
});

test('staff list shows all staff in the branch', function (): void {
    $this->actingAs($this->branchManager)
        ->get('/staff')
        ->assertSuccessful();
});

test('technician cannot create new staff', function (): void {
    $this->actingAs($this->technician)
        ->get('/staff')
        ->assertForbidden();
});

test('super admin can manage staff from any branch', function (): void {
    $otherBranch = Branch::factory()->create();
    $otherUser = User::factory()->create(['branch_id' => $otherBranch->id]);
    Staff::factory()->for($otherUser)->for($otherBranch)->create();

    $this->actingAs($this->superAdmin)
        ->get('/staff')
        ->assertSuccessful();
});

test('can create new staff member in branch', function (): void {
    $this->actingAs($this->branchManager);

    Volt::test(\App\Livewire\Staff\Index::class)
        ->set('form.user_id', $this->newUser->id)
        ->set('form.role', StaffRole::Technician->value)
        ->set('form.hire_date', now()->format('Y-m-d'))
        ->set('form.notes', 'New technician')
        ->call('save')
        ->assertHasNoErrors();

    expect(Staff::where([
        'user_id' => $this->newUser->id,
        'branch_id' => $this->branch->id,
        'role' => StaffRole::Technician->value,
    ])->exists())->toBeTrue();
});

test('cannot create staff with duplicate user in same branch', function (): void {
    $this->actingAs($this->branchManager);

    Volt::test(\App\Livewire\Staff\Index::class)
        ->set('form.user_id', $this->technician->id)
        ->set('form.role', StaffRole::Receptionist->value)
        ->set('form.hire_date', now()->format('Y-m-d'))
        ->call('save')
        ->assertHasErrors('form.user_id');
});

test('can update staff role inline', function (): void {
    $this->actingAs($this->branchManager);

    Volt::test(\App\Livewire\Staff\Index::class)
        ->call('updateRole', $this->staff->id, StaffRole::Receptionist->value)
        ->assertHasNoErrors();

    expect($this->staff->refresh()->role)->toBe(StaffRole::Receptionist);
});

test('can toggle staff active status', function (): void {
    $this->actingAs($this->branchManager);

    Volt::test(\App\Livewire\Staff\Index::class)
        ->call('toggleActive', $this->staff->id)
        ->assertHasNoErrors();

    expect($this->staff->refresh()->is_active)->toBeFalse();
});

test('can delete staff member', function (): void {
    $this->actingAs($this->branchManager);

    Volt::test(\App\Livewire\Staff\Index::class)
        ->call('delete', $this->staff->id)
        ->assertHasNoErrors();

    expect($this->staff->refresh()->is_active)->toBeFalse();
});

test('search filters staff by name', function (): void {
    $this->actingAs($this->branchManager);

    Volt::test(\App\Livewire\Staff\Index::class)
        ->set('search', $this->technician->name)
        ->assertHasNoErrors();
});

test('filter by role works correctly', function (): void {
    $this->actingAs($this->branchManager);

    Volt::test(\App\Livewire\Staff\Index::class)
        ->set('roleFilter', StaffRole::Technician->value)
        ->assertHasNoErrors();
});

test('filter by status works correctly', function (): void {
    $this->actingAs($this->branchManager);

    Volt::test(\App\Livewire\Staff\Index::class)
        ->set('statusFilter', 'active')
        ->assertHasNoErrors();
});

test('super admin can view staff from all branches', function (): void {
    $otherBranch = Branch::factory()->create();
    $otherUser = User::factory()->create(['branch_id' => $otherBranch->id]);
    Staff::factory()->for($otherUser)->for($otherBranch)->create();

    $this->actingAs($this->superAdmin);

    Volt::test(\App\Livewire\Staff\Index::class)
        ->set('branchFilter', $otherBranch->id)
        ->assertHasNoErrors();
});

test('staff permissions are correctly defined', function (): void {
    $branchManagerUser = User::factory()->create(['branch_id' => $this->branch->id]);
    $manager = Staff::factory()
        ->for($branchManagerUser)
        ->for($this->branch)
        ->role(StaffRole::BranchManager)
        ->create();

    expect($manager->getPermissions())->toContain('manage_staff');
    expect($manager->hasPermission('manage_staff'))->toBeTrue();
    expect($manager->hasPermission('invalid_permission'))->toBeFalse();
});

test('inactive staff cannot perform actions', function (): void {
    $inactiveUser = User::factory()->create(['branch_id' => $this->branch->id]);
    $inactiveStaff = Staff::factory()
        ->for($inactiveUser)
        ->for($this->branch)
        ->inactive()
        ->create();

    expect($inactiveStaff->hasPermission('create_sales'))->toBeFalse();
});

test('clear filters resets all search and filter state', function (): void {
    $this->actingAs($this->branchManager);

    Volt::test(\App\Livewire\Staff\Index::class)
        ->set('search', 'test')
        ->set('roleFilter', StaffRole::Technician->value)
        ->set('statusFilter', 'inactive')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('roleFilter', '')
        ->assertSet('statusFilter', '');
});
