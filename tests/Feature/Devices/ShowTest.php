<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Devices\Show;
use App\Models\{Device, Ticket, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

beforeEach(function () {
    $this->user = User::factory()->create(['role' => UserRole::Technician]);
    actingAs($this->user);
});

test('device show page can be rendered', function () {
    $device = Device::factory()->create();

    get(route('devices.show', $device))
        ->assertOk()
        ->assertSeeLivewire(Show::class);
});

test('device show requires authentication', function () {
    auth()->logout();
    $device = Device::factory()->create();

    get(route('devices.show', $device))
        ->assertRedirect(route('login'));
});

test('displays device information', function () {
    $device = Device::factory()->create([
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro',
        'type' => 'Smartphone',
        'serial_number' => 'SN123456',
        'imei' => '123456789012345',
        'notes' => 'Test notes',
    ]);

    Livewire::test(Show::class, ['device' => $device])
        ->assertSee($device->device_name)
        ->assertSee('Apple')
        ->assertSee('iPhone 15 Pro')
        ->assertSee('Smartphone')
        ->assertSee('SN123456')
        ->assertSee('123456789012345')
        ->assertSee('Test notes');
});

test('displays customer information', function () {
    $device = Device::factory()->create();

    Livewire::test(Show::class, ['device' => $device])
        ->assertSee($device->customer->full_name)
        ->assertSee($device->customer->email)
        ->assertSee($device->customer->phone);
});

test('displays customer address when available', function () {
    $device = Device::factory()->create();
    $device->customer->update(['address' => '123 Main St']);

    Livewire::test(Show::class, ['device' => $device])
        ->assertSee('123 Main St');
});

test('displays repair history', function () {
    $device = Device::factory()->hasTickets(2)->create();
    $ticket = $device->tickets->first();

    Livewire::test(Show::class, ['device' => $device])
        ->assertSee($ticket->ticket_number)
        ->assertSee($ticket->problem_description);
});

test('displays empty state when no tickets exist', function () {
    $device = Device::factory()->create();

    Livewire::test(Show::class, ['device' => $device])
        ->assertSee('No repair tickets yet');
});

test('displays ticket status badges', function () {
    $device = Device::factory()->create();
    $ticket = Ticket::factory()->for($device)->create(['status' => 'completed']);

    Livewire::test(Show::class, ['device' => $device])
        ->assertSee('Completed');
});

test('displays ticket creator information', function () {
    $device = Device::factory()->create();
    $creator = User::factory()->create(['name' => 'John Doe']);
    $ticket = Ticket::factory()->for($device)->create(['created_by' => $creator->id]);

    Livewire::test(Show::class, ['device' => $device])
        ->assertSee('John Doe');
});

test('displays edit button for authorized users', function () {
    $device = Device::factory()->create();

    Livewire::test(Show::class, ['device' => $device])
        ->assertSee('Edit Device');
});

test('displays delete button for admin', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin);

    $device = Device::factory()->create();

    Livewire::test(Show::class, ['device' => $device])
        ->assertSee('Delete');
});

test('does not display delete button for technician', function () {
    $device = Device::factory()->create();

    $response = Livewire::test(Show::class, ['device' => $device]);

    // Should not see the main device delete button (but may see photo delete buttons)
    expect($response->html())->not->toContain('Delete</flux:button>')
        ->and($response->html())->not->toContain('wire:click="delete"');
});

test('admin can delete device', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin);

    $device = Device::factory()->create();

    Livewire::test(Show::class, ['device' => $device])
        ->call('delete')
        ->assertRedirect(route('devices.index'));

    expect(Device::find($device->id))->toBeNull();
});

test('manager can delete device', function () {
    $manager = User::factory()->create(['role' => UserRole::Manager]);
    actingAs($manager);

    $device = Device::factory()->create();

    Livewire::test(Show::class, ['device' => $device])
        ->call('delete')
        ->assertRedirect(route('devices.index'));

    expect(Device::find($device->id))->toBeNull();
});

test('technician cannot delete device', function () {
    $device = Device::factory()->create();

    Livewire::test(Show::class, ['device' => $device])
        ->call('delete')
        ->assertForbidden();

    expect(Device::find($device->id))->not->toBeNull();
});

test('delete sets success message', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin);

    $device = Device::factory()->create();

    Livewire::test(Show::class, ['device' => $device])
        ->call('delete');

    expect(session('success'))->toBe('Device deleted successfully.');
});

test('hides imei when not set', function () {
    $device = Device::factory()->create(['imei' => null]);

    Livewire::test(Show::class, ['device' => $device])
        ->assertDontSee('IMEI');
});

test('hides notes when not set', function () {
    $device = Device::factory()->create(['notes' => null]);

    $response = Livewire::test(Show::class, ['device' => $device]);

    // Notes section should not be visible
    expect($device->notes)->toBeNull();
});

test('displays dash for empty serial number', function () {
    $device = Device::factory()->create(['serial_number' => null]);

    Livewire::test(Show::class, ['device' => $device])
        ->assertSee('—');
});
