<?php

declare(strict_types=1);

use App\Models\{Customer, CustomerLoyaltyAccount, LoyaltyTier};

beforeEach(function (): void {
    // Seed tiers with known point thresholds
    $this->bronze = LoyaltyTier::factory()->bronze()->create();
    $this->silver = LoyaltyTier::factory()->silver()->create();
    $this->gold = LoyaltyTier::factory()->gold()->create();
    $this->platinum = LoyaltyTier::factory()->platinum()->create();
});

test('new customer starts with no tier', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->newMember()->create([
        'customer_id' => $customer->id,
    ]);

    expect($loyaltyAccount->loyalty_tier_id)->toBeNull();
});

test('customer progresses to bronze tier automatically', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'total_points' => 100,
        'loyalty_tier_id' => null,
    ]);

    $loyaltyAccount->checkAndUpdateTier();

    $loyaltyAccount->refresh();
    expect($loyaltyAccount->loyalty_tier_id)->toBe($this->bronze->id)
        ->and($loyaltyAccount->tier_achieved_at)->not->toBeNull();
});

test('customer progresses to silver tier at 1000 points', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $this->bronze->id,
        'total_points' => 1000,
    ]);

    $loyaltyAccount->checkAndUpdateTier();

    $loyaltyAccount->refresh();
    expect($loyaltyAccount->loyalty_tier_id)->toBe($this->silver->id);
});

test('customer progresses to gold tier at 5000 points', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $this->silver->id,
        'total_points' => 5000,
    ]);

    $loyaltyAccount->checkAndUpdateTier();

    $loyaltyAccount->refresh();
    expect($loyaltyAccount->loyalty_tier_id)->toBe($this->gold->id);
});

test('customer progresses to platinum tier at 15000 points', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $this->gold->id,
        'total_points' => 15000,
    ]);

    $loyaltyAccount->checkAndUpdateTier();

    $loyaltyAccount->refresh();
    expect($loyaltyAccount->loyalty_tier_id)->toBe($this->platinum->id);
});

test('tier does not change if points are below next tier threshold', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $this->bronze->id,
        'total_points' => 900,
    ]);

    $loyaltyAccount->checkAndUpdateTier();

    $loyaltyAccount->refresh();
    expect($loyaltyAccount->loyalty_tier_id)->toBe($this->bronze->id);
});

test('tier achieved timestamp is updated when tier changes', function (): void {
    $customer = Customer::factory()->create();
    $oldTimestamp = now()->subWeek();

    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $this->bronze->id,
        'total_points' => 1000,
        'tier_achieved_at' => $oldTimestamp,
    ]);

    $loyaltyAccount->checkAndUpdateTier();

    $loyaltyAccount->refresh();
    expect($loyaltyAccount->tier_achieved_at)->not->toBe($oldTimestamp)
        ->and($loyaltyAccount->tier_achieved_at->isToday())->toBeTrue();
});

test('customer gets correct points multiplier for bronze tier', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $this->bronze->id,
    ]);

    expect($loyaltyAccount->getPointsMultiplier())->toBe(1.0);
});

test('customer gets correct points multiplier for silver tier', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $this->silver->id,
    ]);

    expect($loyaltyAccount->getPointsMultiplier())->toBe(1.25);
});

test('customer gets correct points multiplier for gold tier', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $this->gold->id,
    ]);

    expect($loyaltyAccount->getPointsMultiplier())->toBe(1.5);
});

test('customer gets correct points multiplier for platinum tier', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $this->platinum->id,
    ]);

    expect($loyaltyAccount->getPointsMultiplier())->toBe(2.0);
});

test('customer gets correct discount percentage for their tier', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $this->gold->id,
    ]);

    expect($loyaltyAccount->getDiscountPercentage())->toBe(10.0);
});

test('customer with no tier gets default multiplier', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => null,
    ]);

    expect($loyaltyAccount->getPointsMultiplier())->toBe(1.0)
        ->and($loyaltyAccount->getDiscountPercentage())->toBe(0.0);
});

test('inactive tiers are not assigned to customers', function (): void {
    $inactiveTier = LoyaltyTier::factory()->inactive()->create([
        'min_points' => 2000,
        'priority' => 10,
    ]);

    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'total_points' => 2500,
        'loyalty_tier_id' => null,
    ]);

    $loyaltyAccount->checkAndUpdateTier();

    $loyaltyAccount->refresh();
    expect($loyaltyAccount->loyalty_tier_id)->not->toBe($inactiveTier->id);
});

test('customer can skip tiers if they accumulate enough points', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => null,
        'total_points' => 6000, // Enough for Gold
    ]);

    $loyaltyAccount->checkAndUpdateTier();

    $loyaltyAccount->refresh();
    expect($loyaltyAccount->loyalty_tier_id)->toBe($this->gold->id);
});
