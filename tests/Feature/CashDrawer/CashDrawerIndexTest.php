<?php

declare(strict_types=1);

use App\Livewire\CashDrawer\Index;
use App\Models\CashDrawerSession;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('user can view cash drawer index page', function () {
    $user = createAdmin();

    actingAs($user)
        ->get(route('cash-drawer.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

test('index page shows open button when no active session', function () {
    $user = createAdmin();

    actingAs($user)
        ->get(route('cash-drawer.index'))
        ->assertSee('Open Drawer');
});

test('index page shows close button when session is active', function () {
    $user = createAdmin();
    CashDrawerSession::factory()->open()->create();

    actingAs($user)
        ->get(route('cash-drawer.index'))
        ->assertSee('Close Drawer');
});

test('index page displays active session details', function () {
    $user = createAdmin();
    $session = CashDrawerSession::factory()->open()->create([
        'opened_by' => $user->id,
        'opening_balance' => 500.00,
        'cash_sales' => 200.00,
    ]);

    actingAs($user)
        ->get(route('cash-drawer.index'))
        ->assertSee('Active Session')
        ->assertSee($user->name)
        ->assertSee('500.00')
        ->assertSee('200.00');
});

test('index page shows empty state when no sessions exist', function () {
    $user = createAdmin();

    actingAs($user)
        ->get(route('cash-drawer.index'))
        ->assertSee('No cash drawer sessions');
});

test('index page lists all sessions', function () {
    $user = createAdmin();
    $sessions = CashDrawerSession::factory()->count(3)->closed()->create();

    actingAs($user)
        ->get(route('cash-drawer.index'))
        ->assertSee($sessions[0]->openedBy->name)
        ->assertSee($sessions[1]->openedBy->name)
        ->assertSee($sessions[2]->openedBy->name);
});

test('search filters sessions by user name', function () {
    $user = createAdmin();
    $targetUser = User::factory()->create(['name' => 'John Doe']);
    $otherUser = User::factory()->create(['name' => 'Jane Smith']);

    CashDrawerSession::factory()->closed()->create(['opened_by' => $targetUser->id]);
    CashDrawerSession::factory()->closed()->create(['opened_by' => $otherUser->id]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

test('index displays discrepancy for closed sessions', function () {
    $user = createAdmin();
    $session = CashDrawerSession::factory()->closed()->create([
        'expected_balance' => 500.00,
        'actual_balance' => 490.00,
        'discrepancy' => -10.00,
    ]);

    actingAs($user)
        ->get(route('cash-drawer.index'))
        ->assertSee('-10.00');
});

test('pagination works correctly', function () {
    $user = createAdmin();
    CashDrawerSession::factory()->count(20)->closed()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('1')
        ->assertSee('15');
});

test('active session shows expected balance', function () {
    $user = createAdmin();
    CashDrawerSession::factory()->open()->create([
        'opening_balance' => 100.00,
        'cash_sales' => 50.00,
        'cash_in' => 25.00,
        'cash_out' => 15.00,
    ]);

    $expectedBalance = 160.00; // 100 + 50 + 25 - 15

    actingAs($user)
        ->get(route('cash-drawer.index'))
        ->assertSee(number_format($expectedBalance, 2));
});

test('closed session shows all balance details', function () {
    $user = createAdmin();
    CashDrawerSession::factory()->closed()->create([
        'opening_balance' => 100.00,
        'expected_balance' => 150.00,
        'actual_balance' => 155.00,
        'discrepancy' => 5.00,
    ]);

    actingAs($user)
        ->get(route('cash-drawer.index'))
        ->assertSee('100.00')
        ->assertSee('150.00')
        ->assertSee('155.00')
        ->assertSee('5.00');
});
