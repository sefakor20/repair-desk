<?php

declare(strict_types=1);

use App\Enums\CashDrawerStatus;
use App\Enums\CashTransactionType;
use App\Livewire\CashDrawer\OpenDrawer;
use App\Models\CashDrawerSession;
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, assertDatabaseHas};

test('user can view open drawer page when no active session', function (): void {
    $user = createAdmin();

    actingAs($user)
        ->get(route('cash-drawer.open'))
        ->assertOk()
        ->assertSeeLivewire(OpenDrawer::class);
});

test('user cannot view open drawer page when session already open', function (): void {
    $user = createAdmin();

    CashDrawerSession::factory()->open()->create();

    actingAs($user)
        ->get(route('cash-drawer.open'))
        ->assertForbidden();
});

test('user can open cash drawer with valid data', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(OpenDrawer::class)
        ->set('opening_balance', '500.00')
        ->set('opening_notes', 'Starting the day')
        ->call('open')
        ->assertHasNoErrors()
        ->assertRedirect(route('cash-drawer.index'));

    assertDatabaseHas('cash_drawer_sessions', [
        'opened_by' => $user->id,
        'opening_balance' => 500.00,
        'status' => CashDrawerStatus::Open->value,
        'opening_notes' => 'Starting the day',
    ]);
});

test('opening transaction is created when drawer is opened', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(OpenDrawer::class)
        ->set('opening_balance', '300.00')
        ->call('open');

    $session = CashDrawerSession::where('status', 'open')->first();

    expect($session->transactions)->toHaveCount(1);
    expect($session->transactions->first())
        ->type->toBe(CashTransactionType::Opening)
        ->amount->toBe('300.00')
        ->reason->toBe('Cash drawer opened');
});

test('opening balance is required', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(OpenDrawer::class)
        ->set('opening_balance', '')
        ->call('open')
        ->assertHasErrors(['opening_balance' => 'required']);
});

test('opening balance must be numeric', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(OpenDrawer::class)
        ->set('opening_balance', 'not-a-number')
        ->call('open')
        ->assertHasErrors(['opening_balance' => 'numeric']);
});

test('opening balance cannot be negative', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(OpenDrawer::class)
        ->set('opening_balance', '-100')
        ->call('open')
        ->assertHasErrors(['opening_balance' => 'min']);
});

test('opening notes are optional', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(OpenDrawer::class)
        ->set('opening_balance', '100.00')
        ->set('opening_notes', '')
        ->call('open')
        ->assertHasNoErrors();

    assertDatabaseHas('cash_drawer_sessions', [
        'opened_by' => $user->id,
        'opening_notes' => null,
    ]);
});

test('cannot open drawer when another session is already open', function (): void {
    $user = createAdmin();

    CashDrawerSession::factory()->open()->create();

    Livewire::actingAs($user)
        ->test(OpenDrawer::class)
        ->assertForbidden();
});

test('cash drawer initializes with zero values', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(OpenDrawer::class)
        ->set('opening_balance', '200.00')
        ->call('open');

    assertDatabaseHas('cash_drawer_sessions', [
        'cash_sales' => 0,
        'cash_in' => 0,
        'cash_out' => 0,
    ]);
});
