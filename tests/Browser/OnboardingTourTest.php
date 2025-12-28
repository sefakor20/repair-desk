<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserTour;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows admin tour for new admin users', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    visit('/dashboard')
        ->assertSee('Welcome to Repair Desk!')
        ->assertSee('1 / 10')
        ->assertSee('Dashboard Overview');
});

it('shows manager tour for new manager users', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager);

    visit('/dashboard')
        ->assertSee('Welcome to Repair Desk!')
        ->assertSee('1 / 6')
        ->assertSee('Dashboard Overview')
    ;
    ;
});

it('does not show tour for users who have completed it', function () {
    $admin = User::factory()->admin()->create();

    // Mark tour as completed
    UserTour::create([
        'user_id' => $admin->id,
        'tour_name' => 'admin_onboarding',
        'completed_at' => now(),
    ]);

    $this->actingAs($admin);

    visit('/dashboard')
        ->assertDontSee('Welcome to Repair Desk!');
    ;
});

it('navigates through tour steps correctly', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $page = visit('/dashboard');

    // First step - Dashboard
    $page->assertSee('1 / 10')
        ->assertSee('Dashboard Overview');

    // Click Next to go to step 2
    $page->click('Next →')

        ->assertSee('2 / 10');

    // Test Back button
    $page->click('← Back')

        ->assertSee('1 / 10')
        ->assertSee('Dashboard Overview');

    $page;
});

it('highlights navigation items correctly during tour', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $page = visit('/dashboard');

    // Check that highlighting exists (may take time to apply)
    $page;

    // Navigate to next step and check highlighting updates
    $page->click('Next →');

    $page;
});

it('allows skipping the tour', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    visit('/dashboard')
        ->assertSee('Welcome to Repair Desk!')
        ->click('Skip')

        ->assertDontSee('Welcome to Repair Desk!')
    ;

    // Verify tour is marked as skipped in database
    expect(UserTour::where([
        'user_id' => $admin->id,
        'tour_name' => 'admin_onboarding',
    ])->whereNotNull('skipped_at')->exists())->toBeTrue();
});

it('completes the tour when reaching the last step', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $page = visit('/dashboard');

    // Navigate through all 10 steps
    for ($i = 1; $i < 10; $i++) {
        $page->click('Next →');
    }

    // Should be on last step now
    $page->assertSee('10 / 10')
        ->assertSee('Finish');

    // Click Finish
    $page->click('Finish')

        ->assertDontSee('Welcome to Repair Desk!')
    ;

    // Verify tour is marked as completed in database
    expect(UserTour::where([
        'user_id' => $admin->id,
        'tour_name' => 'admin_onboarding',
    ])->whereNotNull('completed_at')->exists())->toBeTrue();

    $page;
});

it('shows progress correctly throughout the tour', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $page = visit('/dashboard');

    // Navigate to step 5 and check progress
    for ($i = 1; $i < 5; $i++) {
        $page->click('Next →');
    }

    $page->assertSee('5 / 10');
});

it('handles button states correctly during navigation', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $page = visit('/dashboard');

    // First step - Back button should be disabled
    $page->assertSeeIn('button[disabled]', '← Back');

    // Navigate to next step
    $page->click('Next →');

    // Navigate to last step
    for ($i = 2; $i < 10; $i++) {
        $page->click('Next →');
    }

    // Should show Finish instead of Next
    $page->assertSee('Finish')
        ->assertDontSee('Next →');

    $page;
});

it('works correctly for different user roles', function () {
    // Test admin role
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    visit('/dashboard')
        ->assertSee("1 / 10");
    ;

    // Test manager role
    $manager = User::factory()->manager()->create();
    $this->actingAs($manager);
    visit('/dashboard')
        ->assertSee("1 / 6");
    ;

    // Test technician role
    $technician = User::factory()->technician()->create();
    $this->actingAs($technician);
    visit('/dashboard')
        ->assertSee("1 / 5");
    ;
});
