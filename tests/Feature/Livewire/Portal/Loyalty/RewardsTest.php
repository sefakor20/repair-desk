<?php

declare(strict_types=1);

use App\Livewire\Portal\Loyalty\Rewards;
use App\Models\{Customer, CustomerLoyaltyAccount, LoyaltyReward, LoyaltyTier};
use Livewire\Livewire;

it('can mount with customer', function () {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create();

    Livewire::test(Rewards::class, ['customer' => $customer])
        ->assertStatus(200)
        ->assertSet('customer.id', $customer->id)
        ->assertSet('account.id', $account->id);
});

it('displays available rewards', function () {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create(['total_points' => 1000]);
    $reward = LoyaltyReward::factory()->create([
        'name' => '10% Discount',
        'points_required' => 500,
        'is_active' => true,
    ]);

    Livewire::test(Rewards::class, ['customer' => $customer])
        ->assertSee('10% Discount')
        ->assertSee('500');
});

it('shows redeem button for eligible rewards', function () {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create(['total_points' => 1000]);
    $reward = LoyaltyReward::factory()->create([
        'name' => 'Free Item',
        'points_required' => 500,
        'is_active' => true,
    ]);

    Livewire::test(Rewards::class, ['customer' => $customer])
        ->assertSee('Redeem Now');
});

it('shows insufficient points message for ineligible rewards', function () {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create(['total_points' => 100]);
    $reward = LoyaltyReward::factory()->create([
        'name' => 'Expensive Item',
        'points_required' => 5000,
        'is_active' => true,
    ]);

    Livewire::test(Rewards::class, ['customer' => $customer])
        ->assertSeeHtml('4,900');
});

it('can open redemption modal', function () {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create(['total_points' => 1000]);
    $reward = LoyaltyReward::factory()->create([
        'name' => 'Test Reward',
        'points_required' => 500,
        'is_active' => true,
    ]);

    Livewire::test(Rewards::class, ['customer' => $customer])
        ->call('selectReward', $reward->id)
        ->assertSet('showRedemptionModal', true)
        ->assertSet('selectedReward.id', $reward->id);
});

it('can close redemption modal', function () {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create(['total_points' => 1000]);
    $reward = LoyaltyReward::factory()->create(['points_required' => 500, 'is_active' => true]);

    Livewire::test(Rewards::class, ['customer' => $customer])
        ->set('selectedReward', $reward)
        ->set('showRedemptionModal', true)
        ->call('closeModal')
        ->assertSet('showRedemptionModal', false)
        ->assertSet('selectedReward', null);
});

it('can redeem reward successfully', function () {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create(['total_points' => 1000]);
    $reward = LoyaltyReward::factory()->create([
        'points_required' => 500,
        'is_active' => true,
        'times_redeemed' => 0,
    ]);

    Livewire::test(Rewards::class, ['customer' => $customer])
        ->set('selectedReward', $reward)
        ->call('redeemReward')
        ->assertDispatched('toast')
        ->assertDispatched('reward-redeemed');

    $account->refresh();
    expect($account->total_points)->toBe(500);
    expect($reward->fresh()->times_redeemed)->toBe(1);
});

it('prevents redeeming ineligible reward', function () {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create(['total_points' => 100]);
    $reward = LoyaltyReward::factory()->create([
        'points_required' => 500,
        'is_active' => true,
    ]);

    Livewire::test(Rewards::class, ['customer' => $customer])
        ->set('selectedReward', $reward)
        ->call('redeemReward')
        ->assertDispatched('toast');

    $account->refresh();
    expect($account->total_points)->toBe(100); // Points unchanged
});

it('filters rewards by customer tier', function () {
    $customer = Customer::factory()->create();
    $tier = LoyaltyTier::factory()->create(['name' => 'Bronze', 'priority' => 1]);
    $account = CustomerLoyaltyAccount::factory()->for($customer)->for($tier, 'loyaltyTier')->create(['total_points' => 1000]);

    $rewardTierRequired = LoyaltyTier::factory()->create(['name' => 'Gold', 'priority' => 3]);
    $rewardForHighTier = LoyaltyReward::factory()->create([
        'name' => 'Gold Reward',
        'min_tier_id' => $rewardTierRequired->id,
        'points_required' => 500,
        'is_active' => true,
    ]);

    $rewardForAll = LoyaltyReward::factory()->create([
        'name' => 'Basic Reward',
        'min_tier_id' => null,
        'points_required' => 500,
        'is_active' => true,
    ]);

    Livewire::test(Rewards::class, ['customer' => $customer])
        ->assertSee('Basic Reward')
        ->assertDontSee('Gold Reward');
});

it('displays empty state when no rewards available', function () {
    $customer = Customer::factory()->create();
    CustomerLoyaltyAccount::factory()->for($customer)->create();

    Livewire::test(Rewards::class, ['customer' => $customer])
        ->assertSee('No rewards available');
});

it('paginates rewards', function () {
    $customer = Customer::factory()->create();
    CustomerLoyaltyAccount::factory()->for($customer)->create(['total_points' => 10000]);

    LoyaltyReward::factory()->count(15)->create([
        'points_required' => 100,
        'is_active' => true,
    ]);

    $component = Livewire::test(Rewards::class, ['customer' => $customer]);

    expect($component->viewData('rewards')->total())->toBe(15);
    expect($component->viewData('rewards')->perPage())->toBe(12);
});
