<?php

declare(strict_types=1);

use App\Livewire\Shifts\Index;
use App\Models\Shift;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('user can view shifts index page', function () {
    $user = createAdmin();

    actingAs($user)
        ->get(route('shifts.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

test('shows open button when no active shift', function () {
    $user = createAdmin();

    $response = actingAs($user)->get(route('shifts.index'));

    $response->assertSee('Open Shift');
});

test('shows close button when shift is active', function () {
    $user = createAdmin();
    Shift::factory()->open()->create(['opened_by' => $user->id]);

    $response = actingAs($user)->get(route('shifts.index'));

    $response->assertSee('Close Shift');
});

test('displays active shift details', function () {
    $user = createAdmin();
    $shift = Shift::factory()->open()->create([
        'opened_by' => $user->id,
        'shift_name' => 'Morning Shift',
    ]);

    $response = actingAs($user)->get(route('shifts.index'));

    $response->assertSee('Morning Shift');
    $response->assertSee('Active Shift');
});

test('shows empty state when no shifts exist', function () {
    $user = createAdmin();

    $response = actingAs($user)->get(route('shifts.index'));

    $response->assertSee('No shifts recorded');
});

test('lists all shifts', function () {
    $user = createAdmin();
    Shift::factory()->closed()->count(3)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee(Shift::first()->shift_name);
});

test('search filters shifts by name', function () {
    $user = createAdmin();
    Shift::factory()->closed()->create(['shift_name' => 'Morning Shift']);
    Shift::factory()->closed()->create(['shift_name' => 'Evening Shift']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'Morning')
        ->assertSee('Morning Shift')
        ->assertDontSee('Evening Shift');
});

test('displays shift sales totals', function () {
    $user = createAdmin();
    Shift::factory()->closed()->create([
        'total_sales' => 1500.00,
        'sales_count' => 10,
    ]);

    $response = actingAs($user)->get(route('shifts.index'));

    $response->assertSee('1,500.00');
});

test('pagination works correctly', function () {
    $user = createAdmin();
    Shift::factory()->closed()->count(20)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertViewHas('shifts', function ($shifts) {
            return $shifts->count() === 15; // Default pagination
        });
});
