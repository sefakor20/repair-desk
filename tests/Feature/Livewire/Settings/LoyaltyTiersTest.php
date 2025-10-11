<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Settings\LoyaltyTiers;
use App\Models\LoyaltyTier;
use App\Models\User;
use Livewire\Livewire;

test('only admin can access loyalty tiers settings page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->assertStatus(200);
});

test('non-admin users cannot access loyalty tiers settings page', function () {
    $user = User::factory()->create(['role' => UserRole::Technician]);

    Livewire::actingAs($user)
        ->test(LoyaltyTiers::class)
        ->assertForbidden();
});

test('loyalty tiers page displays all tiers', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $tiers = LoyaltyTier::factory()->count(3)->create();

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->assertSee($tiers[0]->name)
        ->assertSee($tiers[1]->name)
        ->assertSee($tiers[2]->name);
});

test('admin can create a new loyalty tier', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('openModal')
        ->set('name', 'Diamond')
        ->set('description', 'Elite tier for top customers')
        ->set('min_points', 25000)
        ->set('points_multiplier', 2.5)
        ->set('discount_percentage', 20)
        ->set('color', '#B9F2FF')
        ->set('priority', 5)
        ->set('is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    expect(LoyaltyTier::where('name', 'Diamond')->exists())->toBeTrue();
});

test('tier name is required', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('openModal')
        ->set('name', '')
        ->set('min_points', 1000)
        ->set('points_multiplier', 1.5)
        ->set('discount_percentage', 10)
        ->call('save')
        ->assertHasErrors(['name']);
});

test('min points must be at least 0', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('openModal')
        ->set('name', 'Test Tier')
        ->set('min_points', -100)
        ->set('points_multiplier', 1.5)
        ->set('discount_percentage', 10)
        ->call('save')
        ->assertHasErrors(['min_points']);
});

test('points multiplier must be between 1 and 10', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('openModal')
        ->set('name', 'Test Tier')
        ->set('min_points', 1000)
        ->set('points_multiplier', 0.5) // Too low
        ->set('discount_percentage', 10)
        ->call('save')
        ->assertHasErrors(['points_multiplier']);
});

test('discount percentage must be between 0 and 100', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('openModal')
        ->set('name', 'Test Tier')
        ->set('min_points', 1000)
        ->set('points_multiplier', 1.5)
        ->set('discount_percentage', 150) // Too high
        ->call('save')
        ->assertHasErrors(['discount_percentage']);
});

test('admin can edit an existing loyalty tier', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $tier = LoyaltyTier::factory()->create(['name' => 'Old Name']);

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('openModal', $tier->id)
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertHasNoErrors();

    expect($tier->fresh()->name)->toBe('Updated Name');
});

test('admin can delete a loyalty tier without accounts', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $tier = LoyaltyTier::factory()->create();

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('delete', $tier->id);

    expect(LoyaltyTier::find($tier->id))->toBeNull();
});

test('admin cannot delete tier with active customer accounts', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $tier = LoyaltyTier::factory()->create();
    \App\Models\CustomerLoyaltyAccount::factory()->create(['loyalty_tier_id' => $tier->id]);

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('delete', $tier->id);

    expect(LoyaltyTier::find($tier->id))->not->toBeNull();
});

test('admin can toggle tier active status', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $tier = LoyaltyTier::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('toggleActive', $tier->id);

    expect($tier->fresh()->is_active)->toBeFalse();
});

test('edit modal is pre-populated with tier data', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $tier = LoyaltyTier::factory()->create([
        'name' => 'Gold',
        'min_points' => 5000,
        'points_multiplier' => 1.5,
    ]);

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('openModal', $tier->id)
        ->assertSet('name', 'Gold')
        ->assertSet('min_points', 5000)
        ->assertSet('points_multiplier', 1.5);
});

test('form resets after closing modal', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('openModal')
        ->set('name', 'Test Tier')
        ->call('closeModal')
        ->assertSet('name', '')
        ->assertSet('showModal', false);
});

test('admin can create tier successfully', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('openModal')
        ->set('name', 'Test Tier')
        ->set('min_points', 1000)
        ->set('points_multiplier', 1.5)
        ->set('discount_percentage', 10)
        ->set('priority', 1)
        ->call('save')
        ->assertHasNoErrors();

    expect(LoyaltyTier::where('name', 'Test Tier')->exists())->toBeTrue();
});

test('admin can update tier successfully', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $tier = LoyaltyTier::factory()->create();

    Livewire::actingAs($admin)
        ->test(LoyaltyTiers::class)
        ->call('openModal', $tier->id)
        ->set('name', 'Updated')
        ->call('save')
        ->assertHasNoErrors();

    expect($tier->fresh()->name)->toBe('Updated');
});
