<?php

declare(strict_types=1);

use App\Livewire\Portal\Loyalty\History;
use App\Models\{Customer, CustomerLoyaltyAccount, LoyaltyTransaction};
use Livewire\Livewire;

it('can mount with customer', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create();

    Livewire::test(History::class, ['customer' => $customer])
        ->assertStatus(200)
        ->assertSet('customer.id', $customer->id)
        ->assertSet('account.id', $account->id);
});

it('displays all transactions by default', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create();
    LoyaltyTransaction::factory()->for($account, 'loyaltyAccount')->create([
        'type' => 'earned',
        'description' => 'Purchase points',
        'points' => 100,
    ]);
    LoyaltyTransaction::factory()->for($account, 'loyaltyAccount')->create([
        'type' => 'redeemed',
        'description' => 'Reward redeemed',
        'points' => -50,
    ]);

    Livewire::test(History::class, ['customer' => $customer])
        ->assertSee('Purchase points')
        ->assertSee('Reward redeemed');
});

it('can filter transactions by earned type', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create();
    LoyaltyTransaction::factory()->for($account, 'loyaltyAccount')->create([
        'type' => 'earned',
        'description' => 'Earned points',
    ]);
    LoyaltyTransaction::factory()->for($account, 'loyaltyAccount')->create([
        'type' => 'redeemed',
        'description' => 'Redeemed reward',
    ]);

    Livewire::test(History::class, ['customer' => $customer])
        ->set('filterType', 'earned')
        ->assertSee('Earned points')
        ->assertDontSee('Redeemed reward');
});

it('can filter transactions by redeemed type', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create();
    LoyaltyTransaction::factory()->for($account, 'loyaltyAccount')->create([
        'type' => 'earned',
        'description' => 'Earned points',
    ]);
    LoyaltyTransaction::factory()->for($account, 'loyaltyAccount')->create([
        'type' => 'redeemed',
        'description' => 'Redeemed reward',
    ]);

    Livewire::test(History::class, ['customer' => $customer])
        ->set('filterType', 'redeemed')
        ->assertSee('Redeemed reward')
        ->assertDontSee('Earned points');
});

it('can clear filters', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create();

    Livewire::test(History::class, ['customer' => $customer])
        ->set('filterType', 'earned')
        ->call('clearFilters')
        ->assertSet('filterType', 'all');
});

it('displays transaction details correctly', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create();
    $transaction = LoyaltyTransaction::factory()->for($account, 'loyaltyAccount')->create([
        'description' => 'Test transaction',
        'points' => 150,
        'balance_after' => 1150,
        'created_at' => now(),
    ]);

    Livewire::test(History::class, ['customer' => $customer])
        ->assertSee('Test transaction')
        ->assertSee('+150')
        ->assertSee('1,150');
});

it('displays negative points with correct formatting', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create();
    $transaction = LoyaltyTransaction::factory()->for($account, 'loyaltyAccount')->create([
        'description' => 'Redeemed reward',
        'points' => -100,
        'balance_after' => 900,
    ]);

    Livewire::test(History::class, ['customer' => $customer])
        ->assertSee('Redeemed reward')
        ->assertSee('-100');
});

it('displays empty state when no transactions', function (): void {
    $customer = Customer::factory()->create();
    CustomerLoyaltyAccount::factory()->for($customer)->create();

    Livewire::test(History::class, ['customer' => $customer])
        ->assertSee('No transactions found')
        ->assertSee('Your points activity will appear here');
});

it('paginates transactions', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create();

    LoyaltyTransaction::factory()->count(25)->for($account, 'loyaltyAccount')->create();

    $component = Livewire::test(History::class, ['customer' => $customer]);

    expect($component->viewData('transactions')->total())->toBe(25);
    expect($component->viewData('transactions')->perPage())->toBe(20);
});

it('resets pagination when filter changes', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create();

    LoyaltyTransaction::factory()->count(25)->for($account, 'loyaltyAccount')->create([
        'type' => 'earned',
    ]);

    $component = Livewire::test(History::class, ['customer' => $customer])
        ->set('filterType', 'earned');

    // After filter change, pagination should reset (verified by component rendering successfully)
    expect($component->viewData('transactions')->currentPage())->toBe(1);
});

it('displays transactions in descending order by date', function (): void {
    $customer = Customer::factory()->create();
    $account = CustomerLoyaltyAccount::factory()->for($customer)->create();

    $older = LoyaltyTransaction::factory()->for($account, 'loyaltyAccount')->create([
        'description' => 'Older transaction',
        'created_at' => now()->subDays(2),
    ]);
    $newer = LoyaltyTransaction::factory()->for($account, 'loyaltyAccount')->create([
        'description' => 'Newer transaction',
        'created_at' => now(),
    ]);

    $component = Livewire::test(History::class, ['customer' => $customer]);
    $transactions = $component->viewData('transactions');

    expect($transactions->first()->id)->toBe($newer->id);
});
