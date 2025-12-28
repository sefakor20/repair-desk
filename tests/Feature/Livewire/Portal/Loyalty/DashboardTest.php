<?php

declare(strict_types=1);

use App\Livewire\Portal\Loyalty\Dashboard;
use App\Models\{Customer, CustomerLoyaltyAccount, LoyaltyReward, LoyaltyTier, LoyaltyTransaction};
use Livewire\Livewire;

it('can mount with customer and creates loyalty account if missing', function (): void {
    $customer = Customer::factory()->create();

    Livewire::test(Dashboard::class, ['customer' => $customer])
        ->assertStatus(200)
        ->assertSet('customer.id', $customer->id);

    expect($customer->fresh()->loyaltyAccount)->not->toBeNull();
});

it('displays customer welcome message', function (): void {
    $customer = Customer::factory()->create(['first_name' => 'John']);
    CustomerLoyaltyAccount::factory()->for($customer)->create();

    Livewire::test(Dashboard::class, ['customer' => $customer])
        ->assertSee('Welcome back, John!');
});

it('displays total points correctly', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create([
        'total_points' => 5000,
        'lifetime_points' => 7500,
    ]);

    Livewire::test(Dashboard::class, ['customer' => $customer])
        ->assertSee('5,000')
        ->assertSee('7,500');
});

it('displays current tier information', function (): void {
    $customer = Customer::factory()->create();
    $tier = LoyaltyTier::factory()->create([
        'name' => 'Gold',
        'discount_percentage' => 15,
    ]);
    $account = CustomerLoyaltyAccount::factory()->for($customer)->for($tier, 'loyaltyTier')->create();

    $component = Livewire::test(Dashboard::class, ['customer' => $customer])
        ->assertSee('Gold');

    // Check if discount text is present (may be formatted differently)
    $html = $component->html();
    expect($html)->toContain('15');
    expect($html)->toContain('discount');
});

it('displays no tier message when customer has no tier', function (): void {
    $customer = Customer::factory()->create();
    CustomerLoyaltyAccount::factory()->for($customer)->create(['loyalty_tier_id' => null]);

    Livewire::test(Dashboard::class, ['customer' => $customer])
        ->assertSee('No Tier')
        ->assertSee('Start earning to unlock tiers');
});

it('displays progress to next tier', function (): void {
    $customer = Customer::factory()->create();
    $currentTier = LoyaltyTier::factory()->create([
        'name' => 'Bronze',
        'min_points' => 0,
        'priority' => 1,
    ]);
    $nextTier = LoyaltyTier::factory()->create([
        'name' => 'Silver',
        'min_points' => 1000,
        'priority' => 2,
    ]);
    $account = CustomerLoyaltyAccount::factory()->for($customer)->for($currentTier, 'loyaltyTier')->create([
        'total_points' => 500,
    ]);

    Livewire::test(Dashboard::class, ['customer' => $customer])
        ->assertSee('Progress to Silver')
        ->assertSee('500 more points needed');
});

it('displays available rewards', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create(['total_points' => 1000]);
    $reward = LoyaltyReward::factory()->create([
        'name' => 'Free Coffee',
        'points_required' => 500,
        'is_active' => true,
    ]);

    Livewire::test(Dashboard::class, ['customer' => $customer])
        ->assertSee('Free Coffee')
        ->assertSee('500');
});

it('displays recent transactions', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create();
    LoyaltyTransaction::factory()->for($account, 'loyaltyAccount')->create([
        'description' => 'Purchase at Store',
        'points' => 100,
    ]);

    Livewire::test(Dashboard::class, ['customer' => $customer])
        ->assertSee('Purchase at Store')
        ->assertSee('+100');
});

it('displays empty state for rewards when none available', function (): void {
    $customer = Customer::factory()->create();
    CustomerLoyaltyAccount::factory()->for($customer)->create(['total_points' => 0]);

    Livewire::test(Dashboard::class, ['customer' => $customer])
        ->assertSee('No rewards available yet');
});

it('displays empty state for activity when no transactions', function (): void {
    $customer = Customer::factory()->create();
    CustomerLoyaltyAccount::factory()->for($customer)->create();

    Livewire::test(Dashboard::class, ['customer' => $customer])
        ->assertSee('No activity yet');
});
