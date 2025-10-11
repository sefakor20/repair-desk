<?php

declare(strict_types=1);

use App\Enums\ShiftStatus;
use App\Livewire\Shifts\OpenShift;
use App\Models\Shift;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, assertDatabaseHas};

test('user can view open shift page when no active shift', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('shifts.open'))
        ->assertOk()
        ->assertSeeLivewire(OpenShift::class);
});

test('user cannot view open shift page when shift already open', function () {
    $user = User::factory()->create();

    Shift::factory()->open()->create(['opened_by' => $user->id]);

    actingAs($user)
        ->get(route('shifts.open'))
        ->assertForbidden();
});

test('user can open shift with valid data', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OpenShift::class)
        ->set('shift_name', 'Morning Shift')
        ->set('opening_notes', 'Starting shift')
        ->call('open')
        ->assertHasNoErrors()
        ->assertRedirect(route('shifts.index'));

    assertDatabaseHas('shifts', [
        'opened_by' => $user->id,
        'shift_name' => 'Morning Shift',
        'status' => ShiftStatus::Open->value,
        'opening_notes' => 'Starting shift',
    ]);
});

test('shift name is required', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OpenShift::class)
        ->set('shift_name', '')
        ->call('open')
        ->assertHasErrors(['shift_name' => 'required']);
});

test('shift name cannot exceed 255 characters', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OpenShift::class)
        ->set('shift_name', str_repeat('a', 256))
        ->call('open')
        ->assertHasErrors(['shift_name' => 'max']);
});

test('opening notes are optional', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OpenShift::class)
        ->set('shift_name', 'Evening Shift')
        ->set('opening_notes', '')
        ->call('open')
        ->assertHasNoErrors();

    assertDatabaseHas('shifts', [
        'opened_by' => $user->id,
        'shift_name' => 'Evening Shift',
        'opening_notes' => null,
    ]);
});

test('opening notes cannot exceed 500 characters', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OpenShift::class)
        ->set('shift_name', 'Night Shift')
        ->set('opening_notes', str_repeat('a', 501))
        ->call('open')
        ->assertHasErrors(['opening_notes' => 'max']);
});

test('cannot open shift when user already has open shift', function () {
    $user = User::factory()->create();

    Shift::factory()->open()->create(['opened_by' => $user->id]);

    actingAs($user)
        ->get(route('shifts.open'))
        ->assertForbidden();
});

test('shift initializes with zero values', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OpenShift::class)
        ->set('shift_name', 'Test Shift')
        ->call('open');

    $shift = Shift::where('opened_by', $user->id)->latest()->first();

    expect($shift->total_sales)->toBe('0.00');
    expect($shift->sales_count)->toBe(0);
    expect($shift->cash_sales)->toBe('0.00');
    expect($shift->card_sales)->toBe('0.00');
    expect($shift->mobile_money_sales)->toBe('0.00');
    expect($shift->bank_transfer_sales)->toBe('0.00');
});

test('shift suggests name based on time of day', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(OpenShift::class);

    expect($component->get('shift_name'))->not->toBeEmpty();
});
