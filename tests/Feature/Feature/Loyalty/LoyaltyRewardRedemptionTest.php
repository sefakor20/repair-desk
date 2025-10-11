<?php

declare(strict_types=1);

use App\Models\{Customer, CustomerLoyaltyAccount, LoyaltyReward, LoyaltyTier};
use App\Enums\LoyaltyRewardType;

beforeEach(function () {
    $this->bronze = LoyaltyTier::factory()->bronze()->create();
    $this->silver = LoyaltyTier::factory()->silver()->create();
    $this->gold = LoyaltyTier::factory()->gold()->create();
});

test('customer can redeem reward with sufficient points', function () {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'total_points' => 1000,
    ]);

    $reward = LoyaltyReward::factory()->create([
        'points_required' => 500,
        'is_active' => true,
    ]);

    $result = $reward->redeem($loyaltyAccount);

    expect($result)->toBeTrue();
    $loyaltyAccount->refresh();
    expect($loyaltyAccount->total_points)->toBe(500)
        ->and($reward->fresh()->times_redeemed)->toBe(1);
});

test('customer cannot redeem reward with insufficient points', function () {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'total_points' => 300,
    ]);

    $reward = LoyaltyReward::factory()->create([
        'points_required' => 500,
    ]);

    $result = $reward->redeem($loyaltyAccount);

    expect($result)->toBeFalse();
    $loyaltyAccount->refresh();
    expect($loyaltyAccount->total_points)->toBe(300);
});

test('customer cannot redeem inactive reward', function () {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'total_points' => 1000,
    ]);

    $reward = LoyaltyReward::factory()->inactive()->create([
        'points_required' => 500,
    ]);

    $result = $reward->canBeRedeemedBy($loyaltyAccount);

    expect($result)->toBeFalse();
});

test('redemption creates transaction with negative points', function () {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'total_points' => 1000,
    ]);

    $reward = LoyaltyReward::factory()->create([
        'name' => '10% Discount',
        'points_required' => 500,
    ]);

    $reward->redeem($loyaltyAccount);

    $transaction = $loyaltyAccount->transactions()->latest()->first();

    expect($transaction->type->value)->toBe('redeemed')
        ->and($transaction->points)->toBe(-500)
        ->and($transaction->balance_after)->toBe(500)
        ->and($transaction->description)->toContain('10% Discount');
});

test('reward redemption count increments', function () {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'total_points' => 2000,
    ]);

    $reward = LoyaltyReward::factory()->create([
        'points_required' => 500,
        'times_redeemed' => 5,
    ]);

    $reward->redeem($loyaltyAccount);

    expect($reward->fresh()->times_redeemed)->toBe(6);
});

test('customer cannot redeem reward that exceeded redemption limit', function () {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'total_points' => 1000,
    ]);

    $reward = LoyaltyReward::factory()->limitedQuantity(10)->create([
        'points_required' => 500,
        'times_redeemed' => 10,
    ]);

    $result = $reward->canBeRedeemedBy($loyaltyAccount);

    expect($result)->toBeFalse();
});

test('customer cannot redeem reward before valid_from date', function () {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'total_points' => 1000,
    ]);

    $reward = LoyaltyReward::factory()->create([
        'points_required' => 500,
        'valid_from' => now()->addDays(5),
        'valid_until' => now()->addMonths(1),
    ]);

    $result = $reward->canBeRedeemedBy($loyaltyAccount);

    expect($result)->toBeFalse();
});

test('customer cannot redeem expired reward', function () {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'total_points' => 1000,
    ]);

    $reward = LoyaltyReward::factory()->expired()->create([
        'points_required' => 500,
    ]);

    $result = $reward->canBeRedeemedBy($loyaltyAccount);

    expect($result)->toBeFalse();
});

test('customer cannot redeem tier-restricted reward without required tier', function () {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $this->bronze->id,
        'total_points' => 1000,
    ]);

    $reward = LoyaltyReward::factory()->create([
        'points_required' => 500,
        'min_tier_id' => $this->gold->id,
    ]);

    $result = $reward->canBeRedeemedBy($loyaltyAccount);

    expect($result)->toBeFalse();
});

test('customer can redeem tier-restricted reward with sufficient tier', function () {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $this->gold->id,
        'total_points' => 1000,
    ]);

    $reward = LoyaltyReward::factory()->create([
        'points_required' => 500,
        'min_tier_id' => $this->silver->id,
    ]);

    $result = $reward->canBeRedeemedBy($loyaltyAccount);

    expect($result)->toBeTrue();
});

test('active scope returns only active and valid rewards', function () {
    LoyaltyReward::factory()->create(['is_active' => true]);
    LoyaltyReward::factory()->inactive()->create();
    LoyaltyReward::factory()->expired()->create(['is_active' => true]);

    $activeRewards = LoyaltyReward::active()->count();

    expect($activeRewards)->toBe(1);
});

test('available scope returns rewards that can still be redeemed', function () {
    LoyaltyReward::factory()->create(['is_active' => true]);
    LoyaltyReward::factory()->limitedQuantity(5)->create([
        'is_active' => true,
        'times_redeemed' => 5,
    ]);

    $availableRewards = LoyaltyReward::available()->count();

    expect($availableRewards)->toBe(1);
});

test('discount reward has correct value structure', function () {
    $reward = LoyaltyReward::factory()->discount(15)->create();

    expect($reward->type)->toBe(LoyaltyRewardType::Discount)
        ->and($reward->reward_value['percentage'])->toBe(15);
});

test('voucher reward has correct value structure', function () {
    $reward = LoyaltyReward::factory()->voucher(100)->create();

    expect($reward->type)->toBe(LoyaltyRewardType::Voucher)
        ->and($reward->reward_value['amount'])->toBe(100)
        ->and($reward->reward_value)->toHaveKey('code');
});

test('deduct points throws exception when insufficient balance', function () {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'total_points' => 100,
    ]);

    expect(fn() => $loyaltyAccount->deductPoints(500, 'redeemed', 'Test redemption'))
        ->toThrow(Exception::class, 'Insufficient points balance');
});
