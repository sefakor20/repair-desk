<?php

declare(strict_types=1);

use App\Enums\CashDrawerStatus;
use App\Enums\CashTransactionType;
use App\Livewire\CashDrawer\CloseDrawer;
use App\Models\CashDrawerSession;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, assertDatabaseHas};

test('user can view close drawer page when session is open', function () {
    $user = User::factory()->create();
    CashDrawerSession::factory()->open()->create();

    actingAs($user)
        ->get(route('cash-drawer.close'))
        ->assertOk()
        ->assertSeeLivewire(CloseDrawer::class);
});

test('user cannot view close drawer page when no active session', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('cash-drawer.close'))
        ->assertNotFound();
});

test('user can close cash drawer with matching balance', function () {
    $user = User::factory()->create();
    $session = CashDrawerSession::factory()->open()->create([
        'opening_balance' => 500.00,
        'cash_sales' => 200.00,
        'cash_in' => 50.00,
        'cash_out' => 100.00,
    ]);

    $expectedBalance = 650.00; // 500 + 200 + 50 - 100

    Livewire::actingAs($user)
        ->test(CloseDrawer::class)
        ->assertSet('actual_balance', number_format($expectedBalance, 2, '.', ''))
        ->set('actual_balance', '650.00')
        ->call('close')
        ->assertHasNoErrors()
        ->assertRedirect(route('cash-drawer.index'));

    assertDatabaseHas('cash_drawer_sessions', [
        'id' => $session->id,
        'closed_by' => $user->id,
        'status' => CashDrawerStatus::Closed->value,
        'expected_balance' => 650.00,
        'actual_balance' => 650.00,
        'discrepancy' => 0.00,
    ]);
});

test('user can close cash drawer with overage', function () {
    $user = User::factory()->create();
    $session = CashDrawerSession::factory()->open()->create([
        'opening_balance' => 100.00,
        'cash_sales' => 50.00,
    ]);

    Livewire::actingAs($user)
        ->test(CloseDrawer::class)
        ->set('actual_balance', '160.00')
        ->call('close');

    assertDatabaseHas('cash_drawer_sessions', [
        'id' => $session->id,
        'expected_balance' => 150.00,
        'actual_balance' => 160.00,
        'discrepancy' => 10.00,
    ]);
});

test('user can close cash drawer with shortage', function () {
    $user = User::factory()->create();
    $session = CashDrawerSession::factory()->open()->create([
        'opening_balance' => 100.00,
        'cash_sales' => 50.00,
    ]);

    Livewire::actingAs($user)
        ->test(CloseDrawer::class)
        ->set('actual_balance', '140.00')
        ->call('close');

    assertDatabaseHas('cash_drawer_sessions', [
        'id' => $session->id,
        'expected_balance' => 150.00,
        'actual_balance' => 140.00,
        'discrepancy' => -10.00,
    ]);
});

test('closing transaction is created when drawer is closed', function () {
    $user = User::factory()->create();
    $session = CashDrawerSession::factory()->open()->create([
        'opening_balance' => 100.00,
    ]);

    Livewire::actingAs($user)
        ->test(CloseDrawer::class)
        ->set('actual_balance', '100.00')
        ->call('close');

    $session->refresh();

    expect($session->transactions)->toHaveCount(1);
    expect($session->transactions->first())
        ->type->toBe(CashTransactionType::Closing)
        ->amount->toBe('100.00')
        ->reason->toBe('Cash drawer closed');
});

test('actual balance is required', function () {
    $user = User::factory()->create();
    CashDrawerSession::factory()->open()->create();

    Livewire::actingAs($user)
        ->test(CloseDrawer::class)
        ->set('actual_balance', '')
        ->call('close')
        ->assertHasErrors(['actual_balance' => 'required']);
});

test('actual balance must be numeric', function () {
    $user = User::factory()->create();
    CashDrawerSession::factory()->open()->create();

    Livewire::actingAs($user)
        ->test(CloseDrawer::class)
        ->set('actual_balance', 'not-a-number')
        ->call('close')
        ->assertHasErrors(['actual_balance' => 'numeric']);
});

test('actual balance cannot be negative', function () {
    $user = User::factory()->create();
    CashDrawerSession::factory()->open()->create();

    Livewire::actingAs($user)
        ->test(CloseDrawer::class)
        ->set('actual_balance', '-50')
        ->call('close')
        ->assertHasErrors(['actual_balance' => 'min']);
});

test('closing notes are optional', function () {
    $user = User::factory()->create();
    $session = CashDrawerSession::factory()->open()->create();

    Livewire::actingAs($user)
        ->test(CloseDrawer::class)
        ->set('actual_balance', '100.00')
        ->set('closing_notes', '')
        ->call('close')
        ->assertHasNoErrors();

    assertDatabaseHas('cash_drawer_sessions', [
        'id' => $session->id,
        'closing_notes' => null,
    ]);
});

test('expected balance is calculated correctly', function () {
    $user = User::factory()->create();
    CashDrawerSession::factory()->open()->create([
        'opening_balance' => 100.00,
        'cash_sales' => 50.00,
        'cash_in' => 25.00,
        'cash_out' => 15.00,
    ]);

    Livewire::actingAs($user)
        ->test(CloseDrawer::class)
        ->assertViewHas('expectedBalance', 160.00);
});

test('discrepancy is calculated dynamically', function () {
    $user = User::factory()->create();
    CashDrawerSession::factory()->open()->create([
        'opening_balance' => 100.00,
    ]);

    Livewire::actingAs($user)
        ->test(CloseDrawer::class)
        ->set('actual_balance', '110.00')
        ->assertViewHas('discrepancy', 10.00);
});

test('closing notes can include discrepancy explanation', function () {
    $user = User::factory()->create();
    $session = CashDrawerSession::factory()->open()->create();

    Livewire::actingAs($user)
        ->test(CloseDrawer::class)
        ->set('actual_balance', '90.00')
        ->set('closing_notes', 'Customer refund not recorded')
        ->call('close');

    assertDatabaseHas('cash_drawer_sessions', [
        'id' => $session->id,
        'closing_notes' => 'Customer refund not recorded',
    ]);
});
