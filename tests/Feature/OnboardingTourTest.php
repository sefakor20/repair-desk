<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\OnboardingTour;
use App\Models\User;
use App\Services\TourService;
use Livewire\Volt\Volt;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'role' => UserRole::Admin,
    ]);
});

test('tour service returns correct tour name for user role', function (): void {
    $tourService = app(TourService::class);

    $adminUser = User::factory()->create(['role' => UserRole::Admin]);
    $managerUser = User::factory()->create(['role' => UserRole::Manager]);
    $technicianUser = User::factory()->create(['role' => UserRole::Technician]);
    $frontDeskUser = User::factory()->create(['role' => UserRole::FrontDesk]);

    expect($tourService->getTourNameForUser($adminUser))->toBe('admin_onboarding');
    expect($tourService->getTourNameForUser($managerUser))->toBe('manager_onboarding');
    expect($tourService->getTourNameForUser($technicianUser))->toBe('technician_onboarding');
    expect($tourService->getTourNameForUser($frontDeskUser))->toBe('front_desk_onboarding');
});

test('tour should show for new user without completed tour', function (): void {
    $tourService = app(TourService::class);

    expect($tourService->shouldShowTour($this->user))->toBeTrue();
});

test('tour should not show for user with completed tour', function (): void {
    $tourService = app(TourService::class);

    // Create completed tour record
    $this->user->tours()->create([
        'tour_name' => 'admin_onboarding',
        'is_completed' => true,
    ]);

    expect($tourService->shouldShowTour($this->user))->toBeFalse();
});

test('tour returns role-specific steps', function (): void {
    $tourService = app(TourService::class);

    $steps = $tourService->getTourSteps($this->user);

    expect($steps)->toBeArray();
    expect($steps)->not->toBeEmpty();
    expect($steps[0])->toHaveKeys(['id', 'title', 'content', 'target', 'position']);
});

test('admin gets comprehensive tour steps', function (): void {
    $tourService = app(TourService::class);
    $adminUser = User::factory()->create(['role' => UserRole::Admin]);

    $steps = $tourService->getTourSteps($adminUser);

    // Admin should have the most comprehensive tour
    expect(count($steps))->toBeGreaterThan(8);
    expect(array_column($steps, 'id'))->toContain('manage_users');
    expect(array_column($steps, 'id'))->toContain('manage_branches');
    expect(array_column($steps, 'id'))->toContain('inventory_system');
});

test('technician gets focused tour steps', function (): void {
    $tourService = app(TourService::class);
    $technicianUser = User::factory()->create(['role' => UserRole::Technician]);

    $steps = $tourService->getTourSteps($technicianUser);

    // Technician should have focused, work-specific tour
    expect(count($steps))->toBeLessThan(8);
    expect(array_column($steps, 'id'))->toContain('assigned_tickets');
    expect(array_column($steps, 'id'))->toContain('ticket_workflow');
});

test('onboarding tour component initializes correctly for new user', function (): void {
    $this->actingAs($this->user);

    $tourSteps = Volt::test(OnboardingTour::class)
        ->assertSet('showTour', true)
        ->assertSet('currentStep', 0)
        ->get('tourSteps');

    expect(count($tourSteps))->toBeGreaterThan(0);
});

test('onboarding tour component does not show for user with completed tour', function (): void {
    // Mark tour as completed
    $this->user->tours()->create([
        'tour_name' => 'admin_onboarding',
        'is_completed' => true,
    ]);

    $this->actingAs($this->user);

    Volt::test(OnboardingTour::class)
        ->assertSet('showTour', false);
});

test('user can navigate through tour steps', function (): void {
    $this->actingAs($this->user);

    Volt::test(OnboardingTour::class)
        ->assertSet('showTour', true)
        ->assertSet('currentStep', 0)
        ->call('nextStep')
        ->assertSet('currentStep', 1)
        ->call('previousStep')
        ->assertSet('currentStep', 0);
});

test('user can skip tour', function (): void {
    $this->actingAs($this->user);

    Volt::test(OnboardingTour::class)
        ->assertSet('showTour', true)
        ->call('skipTour')
        ->assertSet('showTour', false);

    // Verify tour is marked as skipped in database
    expect($this->user->fresh()->hasCompletedTour('admin_onboarding'))->toBeTrue();
});

test('user can complete tour', function (): void {
    $this->actingAs($this->user);

    Volt::test(OnboardingTour::class)
        ->assertSet('showTour', true)
        ->call('completeTour')
        ->assertSet('showTour', false);

    // Verify tour is marked as completed in database
    expect($this->user->fresh()->hasCompletedTour('admin_onboarding'))->toBeTrue();
});

test('tour tracks individual step completion', function (): void {
    $tourService = app(TourService::class);

    $tour = $this->user->getOrCreateTour('admin_onboarding');

    expect($tour->hasCompletedStep('dashboard_overview'))->toBeFalse();

    $tourService->markStepCompleted($this->user, 'dashboard_overview');

    expect($tour->fresh()->hasCompletedStep('dashboard_overview'))->toBeTrue();
});
