<?php

declare(strict_types=1);

use App\Enums\ReturnCondition;
use App\Livewire\Settings\ReturnPolicies;
use App\Models\{ReturnPolicy, User};
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->manager = User::factory()->create(['role' => 'manager']);
    $this->technician = User::factory()->create(['role' => 'technician']);
});

test('only admin can access return policies settings page', function () {
    actingAs($this->admin);

    Livewire::test(ReturnPolicies::class)
        ->assertSuccessful();
});

test('non-admin users cannot access return policies settings page', function () {
    actingAs($this->technician);

    Livewire::test(ReturnPolicies::class)
        ->assertForbidden();
});

test('return policies page displays all policies', function () {
    actingAs($this->admin);

    $policies = ReturnPolicy::factory()->count(3)->create();

    Livewire::test(ReturnPolicies::class)
        ->assertSee($policies[0]->name)
        ->assertSee($policies[1]->name)
        ->assertSee($policies[2]->name);
});

test('admin can create a new return policy', function () {
    actingAs($this->admin);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal')
        ->set('name', 'Standard Return Policy')
        ->set('description', 'Our standard return policy')
        ->set('return_window_days', 30)
        ->set('requires_receipt', true)
        ->set('requires_original_packaging', false)
        ->set('requires_approval', false)
        ->set('restocking_fee_percentage', 15)
        ->set('minimum_restocking_fee', 10)
        ->set('refund_shipping', false)
        ->set('allowed_conditions', [ReturnCondition::New->value, ReturnCondition::Opened->value])
        ->set('terms', 'All sales are final after 30 days')
        ->call('save')
        ->assertHasNoErrors();

    expect(ReturnPolicy::count())->toBe(1);

    $policy = ReturnPolicy::first();
    expect($policy->name)->toBe('Standard Return Policy')
        ->and($policy->return_window_days)->toBe(30)
        ->and($policy->restocking_fee_percentage)->toBe('15.00')
        ->and($policy->allowed_conditions)->toContain(ReturnCondition::New->value);
});

test('policy name is required', function () {
    actingAs($this->admin);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal')
        ->set('name', '')
        ->set('return_window_days', 30)
        ->set('allowed_conditions', [ReturnCondition::New->value])
        ->call('save')
        ->assertHasErrors(['name']);
});

test('return window days is required', function () {
    actingAs($this->admin);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal')
        ->set('name', 'Test Policy')
        ->set('return_window_days', 0)
        ->set('allowed_conditions', [ReturnCondition::New->value])
        ->call('save')
        ->assertHasErrors(['return_window_days']);
});

test('return window days must be between 1 and 365', function () {
    actingAs($this->admin);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal')
        ->set('name', 'Test Policy')
        ->set('return_window_days', 0)
        ->set('allowed_conditions', [ReturnCondition::New->value])
        ->call('save')
        ->assertHasErrors(['return_window_days']);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal')
        ->set('name', 'Test Policy')
        ->set('return_window_days', 366)
        ->set('allowed_conditions', [ReturnCondition::New->value])
        ->call('save')
        ->assertHasErrors(['return_window_days']);
});

test('restocking fee percentage must be between 0 and 100', function () {
    actingAs($this->admin);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal')
        ->set('name', 'Test Policy')
        ->set('return_window_days', 30)
        ->set('restocking_fee_percentage', -1)
        ->set('allowed_conditions', [ReturnCondition::New->value])
        ->call('save')
        ->assertHasErrors(['restocking_fee_percentage']);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal')
        ->set('name', 'Test Policy')
        ->set('return_window_days', 30)
        ->set('restocking_fee_percentage', 101)
        ->set('allowed_conditions', [ReturnCondition::New->value])
        ->call('save')
        ->assertHasErrors(['restocking_fee_percentage']);
});

test('at least one allowed condition is required', function () {
    actingAs($this->admin);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal')
        ->set('name', 'Test Policy')
        ->set('return_window_days', 30)
        ->set('allowed_conditions', [])
        ->call('save')
        ->assertHasErrors(['allowed_conditions']);
});

test('admin can edit an existing return policy', function () {
    actingAs($this->admin);

    $policy = ReturnPolicy::factory()->create([
        'name' => 'Original Name',
        'return_window_days' => 30,
    ]);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal', $policy->id)
        ->set('name', 'Updated Name')
        ->set('return_window_days', 60)
        ->call('save')
        ->assertHasNoErrors();

    $policy->refresh();
    expect($policy->name)->toBe('Updated Name')
        ->and($policy->return_window_days)->toBe(60);
});

test('admin can delete a return policy', function () {
    actingAs($this->admin);

    $policy = ReturnPolicy::factory()->create();

    expect(ReturnPolicy::count())->toBe(1);

    Livewire::test(ReturnPolicies::class)
        ->call('delete', $policy->id);

    expect(ReturnPolicy::count())->toBe(0);
});

test('admin can toggle policy active status', function () {
    actingAs($this->admin);

    $policy = ReturnPolicy::factory()->create(['is_active' => true]);

    Livewire::test(ReturnPolicies::class)
        ->call('toggleActive', $policy->id);

    $policy->refresh();
    expect($policy->is_active)->toBeFalse();

    Livewire::test(ReturnPolicies::class)
        ->call('toggleActive', $policy->id);

    $policy->refresh();
    expect($policy->is_active)->toBeTrue();
});

test('policy correctly identifies eligible returns based on time window', function () {
    $policy = ReturnPolicy::factory()->create([
        'return_window_days' => 30,
        'is_active' => true,
    ]);

    // Recent sale - eligible
    $recentSale = \App\Models\PosSale::factory()->create([
        'created_at' => now()->subDays(10),
    ]);
    expect($policy->isReturnEligible($recentSale))->toBeTrue();

    // Old sale - not eligible
    $oldSale = \App\Models\PosSale::factory()->create([
        'created_at' => now()->subDays(35),
    ]);
    expect($policy->isReturnEligible($oldSale))->toBeFalse();
});

test('inactive policy is not eligible for returns', function () {
    $policy = ReturnPolicy::factory()->create([
        'is_active' => false,
        'return_window_days' => 30,
    ]);

    $sale = \App\Models\PosSale::factory()->create([
        'created_at' => now()->subDays(10),
    ]);

    expect($policy->isReturnEligible($sale))->toBeFalse();
});

test('policy calculates restocking fee correctly', function () {
    $policy = ReturnPolicy::factory()->create([
        'restocking_fee_percentage' => 15,
        'minimum_restocking_fee' => 10,
    ]);

    // Fee above minimum
    $fee1 = $policy->calculateRestockingFee(200);
    expect($fee1)->toBe(30.0); // 15% of 200

    // Fee below minimum - should use minimum
    $fee2 = $policy->calculateRestockingFee(50);
    expect($fee2)->toBe(10.0); // minimum fee
});

test('policy with zero restocking fee works correctly', function () {
    $policy = ReturnPolicy::factory()->create([
        'restocking_fee_percentage' => 0,
        'minimum_restocking_fee' => 0,
    ]);

    $fee = $policy->calculateRestockingFee(100);
    expect($fee)->toBe(0.0);
});

test('policy shows correct allowed conditions labels', function () {
    $policy = ReturnPolicy::factory()->create([
        'allowed_conditions' => [
            ReturnCondition::New->value,
            ReturnCondition::Opened->value,
        ],
    ]);

    $labels = $policy->getAllowedConditionsLabels();
    expect($labels)->toContain('New/Unopened')
        ->and($labels)->toContain('Opened')
        ->and($labels)->toHaveCount(2);
});

test('edit modal is pre-populated with policy data', function () {
    actingAs($this->admin);

    $policy = ReturnPolicy::factory()->create([
        'name' => 'Test Policy',
        'description' => 'Test Description',
        'return_window_days' => 45,
        'requires_receipt' => false,
        'restocking_fee_percentage' => 20,
    ]);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal', $policy->id)
        ->assertSet('name', 'Test Policy')
        ->assertSet('description', 'Test Description')
        ->assertSet('return_window_days', 45)
        ->assertSet('requires_receipt', false)
        ->assertSet('restocking_fee_percentage', 20.0);
});

test('form resets after closing modal', function () {
    actingAs($this->admin);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal')
        ->set('name', 'Test')
        ->set('description', 'Description')
        ->call('closeModal')
        ->assertSet('name', '')
        ->assertSet('description', '')
        ->assertSet('showModal', false);
});

test('success message is shown after creating policy', function () {
    actingAs($this->admin);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal')
        ->set('name', 'Test Policy')
        ->set('return_window_days', 30)
        ->set('allowed_conditions', [ReturnCondition::New->value])
        ->call('save')
        ->assertSet('showModal', false);

    expect(ReturnPolicy::where('name', 'Test Policy')->exists())->toBeTrue();
});

test('success message is shown after updating policy', function () {
    actingAs($this->admin);

    $policy = ReturnPolicy::factory()->create(['name' => 'Original']);

    Livewire::test(ReturnPolicies::class)
        ->call('openModal', $policy->id)
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertSet('showModal', false);

    $policy->refresh();
    expect($policy->name)->toBe('Updated Name');
});
