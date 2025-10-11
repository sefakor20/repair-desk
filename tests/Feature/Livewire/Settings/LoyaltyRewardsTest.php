<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Settings\LoyaltyRewards;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyTier;
use App\Models\User;
use Livewire\Livewire;

test('only admin can access loyalty rewards settings page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->assertStatus(200);
});

test('non-admin users cannot access loyalty rewards settings page', function () {
    $user = User::factory()->create(['role' => UserRole::Technician]);

    Livewire::actingAs($user)
        ->test(LoyaltyRewards::class)
        ->assertForbidden();
});

test('loyalty rewards page displays all rewards', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $rewards = LoyaltyReward::factory()->count(3)->create();

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->assertSee($rewards[0]->name)
        ->assertSee($rewards[1]->name)
        ->assertSee($rewards[2]->name);
});

test('admin can create a discount reward', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('openModal')
        ->set('name', '10% Off')
        ->set('description', 'Get 10% off your next purchase')
        ->set('type', 'discount')
        ->set('points_required', 500)
        ->set('discount_percentage', 10)
        ->set('is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    $reward = LoyaltyReward::where('name', '10% Off')->first();
    expect($reward)->not->toBeNull();
    expect($reward->reward_value['percentage'])->toBe(10);
});

test('admin can create a voucher reward', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('openModal')
        ->set('name', '$25 Voucher')
        ->set('type', 'voucher')
        ->set('points_required', 1000)
        ->set('voucher_amount', 25)
        ->call('save')
        ->assertHasNoErrors();

    $reward = LoyaltyReward::where('name', '$25 Voucher')->first();
    expect($reward->reward_value['amount'])->toBe(25);
});

test('reward name is required', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('openModal')
        ->set('name', '')
        ->set('type', 'discount')
        ->set('points_required', 500)
        ->call('save')
        ->assertHasErrors(['name']);
});

test('reward type is required', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('openModal')
        ->set('name', 'Test Reward')
        ->set('type', '')
        ->set('points_required', 500)
        ->call('save')
        ->assertHasErrors(['type']);
});

test('points required must be at least 1', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('openModal')
        ->set('name', 'Test Reward')
        ->set('type', 'discount')
        ->set('points_required', 0)
        ->call('save')
        ->assertHasErrors(['points_required']);
});

test('valid until must be after valid from', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('openModal')
        ->set('name', 'Test Reward')
        ->set('type', 'discount')
        ->set('points_required', 500)
        ->set('valid_from', '2025-12-31')
        ->set('valid_until', '2025-01-01') // Before valid_from
        ->call('save')
        ->assertHasErrors(['valid_until']);
});

test('admin can edit an existing reward', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $reward = LoyaltyReward::factory()->create(['name' => 'Old Name']);

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('openModal', $reward->id)
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertHasNoErrors();

    expect($reward->fresh()->name)->toBe('Updated Name');
});

test('admin can delete a reward', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $reward = LoyaltyReward::factory()->create();

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('delete', $reward->id);

    expect(LoyaltyReward::find($reward->id))->toBeNull();
});

test('admin can toggle reward active status', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $reward = LoyaltyReward::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('toggleActive', $reward->id);

    expect($reward->fresh()->is_active)->toBeFalse();
});

test('admin can create tier-restricted reward', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $tier = LoyaltyTier::factory()->create();

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('openModal')
        ->set('name', 'Exclusive Reward')
        ->set('type', 'discount')
        ->set('points_required', 1000)
        ->set('discount_percentage', 15)
        ->set('min_tier_id', $tier->id)
        ->call('save')
        ->assertHasNoErrors();

    $reward = LoyaltyReward::where('name', 'Exclusive Reward')->first();
    expect($reward->min_tier_id)->toBe($tier->id);
});

test('edit modal is pre-populated with reward data', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $reward = LoyaltyReward::factory()->discount(15)->create([
        'name' => 'Test Reward',
        'points_required' => 750,
    ]);

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('openModal', $reward->id)
        ->assertSet('name', 'Test Reward')
        ->assertSet('points_required', 750)
        ->assertSet('type', 'discount');
});

test('form resets after closing modal', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('openModal')
        ->set('name', 'Test Reward')
        ->call('closeModal')
        ->assertSet('name', '')
        ->assertSet('showModal', false);
});

test('admin can create reward successfully', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('openModal')
        ->set('name', 'Test Reward')
        ->set('type', 'discount')
        ->set('points_required', 500)
        ->set('discount_percentage', 10)
        ->call('save')
        ->assertHasNoErrors();

    expect(LoyaltyReward::where('name', 'Test Reward')->exists())->toBeTrue();
});

test('admin can update reward successfully', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $reward = LoyaltyReward::factory()->create();

    Livewire::actingAs($admin)
        ->test(LoyaltyRewards::class)
        ->call('openModal', $reward->id)
        ->set('name', 'Updated')
        ->call('save')
        ->assertHasNoErrors();

    expect($reward->fresh()->name)->toBe('Updated');
});
