<?php

declare(strict_types=1);

use App\Enums\ShiftStatus;
use App\Livewire\Shifts\CloseShift;
use App\Models\Shift;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, assertDatabaseHas};

test('user can view close shift page when shift is open', function () {
    $user = User::factory()->create();
    Shift::factory()->open()->create(['opened_by' => $user->id]);

    actingAs($user)
        ->get(route('shifts.close'))
        ->assertOk()
        ->assertSeeLivewire(CloseShift::class);
});

test('user cannot view close shift page when no active shift', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('shifts.close'))
        ->assertNotFound();
});

test('user can close shift successfully', function () {
    $user = User::factory()->create();
    $shift = Shift::factory()->open()->create(['opened_by' => $user->id]);

    Livewire::actingAs($user)
        ->test(CloseShift::class)
        ->set('closing_notes', 'End of shift')
        ->call('close')
        ->assertHasNoErrors()
        ->assertRedirect(route('shifts.index'));

    assertDatabaseHas('shifts', [
        'id' => $shift->id,
        'closed_by' => $user->id,
        'status' => ShiftStatus::Closed->value,
        'closing_notes' => 'End of shift',
    ]);
});

test('closing notes are optional', function () {
    $user = User::factory()->create();
    $shift = Shift::factory()->open()->create(['opened_by' => $user->id]);

    Livewire::actingAs($user)
        ->test(CloseShift::class)
        ->set('closing_notes', '')
        ->call('close')
        ->assertHasNoErrors();

    expect(Shift::find($shift->id)->closing_notes)->toBeNull();
});

test('closing notes cannot exceed 500 characters', function () {
    $user = User::factory()->create();
    Shift::factory()->open()->create(['opened_by' => $user->id]);

    Livewire::actingAs($user)
        ->test(CloseShift::class)
        ->set('closing_notes', str_repeat('a', 501))
        ->call('close')
        ->assertHasErrors(['closing_notes' => 'max']);
});

test('user cannot close another users shift', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    Shift::factory()->open()->create(['opened_by' => $user1->id]);

    actingAs($user2)
        ->get(route('shifts.close'))
        ->assertNotFound();
});

test('shift sets ended_at timestamp when closed', function () {
    $user = User::factory()->create();
    $shift = Shift::factory()->open()->create(['opened_by' => $user->id]);

    Livewire::actingAs($user)
        ->test(CloseShift::class)
        ->call('close');

    $closedShift = Shift::find($shift->id);
    expect($closedShift->ended_at)->not->toBeNull();
    expect($closedShift->closed_by)->toBe($user->id);
});
