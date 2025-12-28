<?php

declare(strict_types=1);

use App\Models\{Customer, CustomerLoyaltyAccount, InventoryItem, LoyaltyTier, PosSale, User};
use App\Enums\{PosSaleStatus};

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);

    // Seed tiers
    LoyaltyTier::factory()->bronze()->create();
    LoyaltyTier::factory()->silver()->create();
    LoyaltyTier::factory()->gold()->create();
    LoyaltyTier::factory()->platinum()->create();
});

test('customer earns points when completing a purchase', function (): void {
    $customer = Customer::factory()->create();
    $item = InventoryItem::factory()->create(['selling_price' => 100]);

    $sale = PosSale::factory()->create([
        'customer_id' => $customer->id,
        'total_amount' => 150.00,
        'status' => PosSaleStatus::Completed,
    ]);

    $sale->items()->create([
        'inventory_item_id' => $item->id,
        'quantity' => 1,
        'unit_price' => 100,
        'subtotal' => 100,
    ]);

    $loyaltyAccount = $customer->loyaltyAccount;
    expect($loyaltyAccount)->not->toBeNull()
        ->and($loyaltyAccount->total_points)->toBe(150)
        ->and($loyaltyAccount->lifetime_points)->toBe(150)
        ->and($loyaltyAccount->loyalty_tier_id)->not->toBeNull(); // Should get bronze tier
});

test('points are calculated as 1 point per dollar spent', function (): void {
    $customer = Customer::factory()->create();

    PosSale::factory()->create([
        'customer_id' => $customer->id,
        'total_amount' => 250.75,
        'status' => PosSaleStatus::Completed,
    ]);

    $loyaltyAccount = $customer->loyaltyAccount;
    expect($loyaltyAccount->total_points)->toBe(250); // floor(250.75)
});

test('loyalty account is created automatically on first purchase', function (): void {
    $customer = Customer::factory()->create();

    expect(CustomerLoyaltyAccount::where('customer_id', $customer->id)->exists())->toBeFalse();

    PosSale::factory()->create([
        'customer_id' => $customer->id,
        'total_amount' => 100.00,
        'status' => PosSaleStatus::Completed,
    ]);

    expect(CustomerLoyaltyAccount::where('customer_id', $customer->id)->exists())->toBeTrue();
});

test('points multiplier is applied based on customer tier', function (): void {
    $silverTier = LoyaltyTier::where('name', 'Silver')->first();
    $customer = Customer::factory()->create();

    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => $silverTier->id,
        'total_points' => 2000,
    ]);

    PosSale::factory()->create([
        'customer_id' => $customer->id,
        'total_amount' => 100.00,
        'status' => PosSaleStatus::Completed,
    ]);

    $loyaltyAccount->refresh();
    // 100 base points * 1.25 multiplier = 125 points
    expect($loyaltyAccount->total_points)->toBe(2125);
});

test('no points awarded for sales without customer', function (): void {
    PosSale::factory()->create([
        'customer_id' => null,
        'total_amount' => 100.00,
        'status' => PosSaleStatus::Completed,
    ]);

    expect(CustomerLoyaltyAccount::count())->toBe(0);
});

test('points transaction is recorded with correct details', function (): void {
    $customer = Customer::factory()->create();

    $sale = PosSale::factory()->create([
        'customer_id' => $customer->id,
        'total_amount' => 150.00,
        'status' => PosSaleStatus::Completed,
    ]);

    $loyaltyAccount = $customer->loyaltyAccount;
    $transaction = $loyaltyAccount->transactions()->first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->type->value)->toBe('earned')
        ->and($transaction->points)->toBe(150)
        ->and($transaction->balance_after)->toBe(150)
        ->and($transaction->description)->toContain($sale->sale_number);
});

test('lifetime points accumulate separately from total points', function (): void {
    $customer = Customer::factory()->create();
    $loyaltyAccount = CustomerLoyaltyAccount::factory()->create([
        'customer_id' => $customer->id,
        'loyalty_tier_id' => null,
        'total_points' => 100, // Keep below bronze threshold
        'lifetime_points' => 1000,
    ]);

    PosSale::factory()->create([
        'customer_id' => $customer->id,
        'total_amount' => 50.00,
        'status' => PosSaleStatus::Completed,
    ]);

    $loyaltyAccount->refresh();
    expect($loyaltyAccount->total_points)->toBe(150)
        ->and($loyaltyAccount->lifetime_points)->toBe(1050);
});

test('points are awarded even for small purchases', function (): void {
    $customer = Customer::factory()->create();

    PosSale::factory()->create([
        'customer_id' => $customer->id,
        'total_amount' => 5.50,
        'status' => PosSaleStatus::Completed,
    ]);

    $loyaltyAccount = $customer->loyaltyAccount;
    expect($loyaltyAccount->total_points)->toBe(5);
});

test('zero points for purchases under 1 dollar', function (): void {
    $customer = Customer::factory()->create();

    PosSale::factory()->create([
        'customer_id' => $customer->id,
        'total_amount' => 0.75,
        'status' => PosSaleStatus::Completed,
    ]);

    $loyaltyAccount = $customer->loyaltyAccount;
    expect($loyaltyAccount->total_points)->toBe(0);
});
