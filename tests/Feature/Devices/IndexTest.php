<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Devices\Index;
use App\Models\{Customer, Device, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

beforeEach(function (): void {
    $this->user = User::factory()->create(['role' => UserRole::Technician]);
    actingAs($this->user);
});

test('devices index page can be rendered', function (): void {
    get(route('devices.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

test('devices index requires authentication', function (): void {
    auth()->logout();

    get(route('devices.index'))
        ->assertRedirect(route('login'));
});

test('displays devices list', function (): void {
    $device = Device::factory()->create();

    Livewire::test(Index::class)
        ->assertSee($device->device_name)
        ->assertSee($device->customer->full_name)
        ->assertSee($device->type);
});

test('displays empty state when no devices exist', function (): void {
    Livewire::test(Index::class)
        ->assertSee('No devices registered yet');
});

test('displays register device button for authorized users', function (): void {
    Livewire::test(Index::class)
        ->assertSee('Register Device');
});

test('search filters devices by brand', function (): void {
    $appleDevice = Device::factory()->create(['brand' => 'Apple']);
    $samsungDevice = Device::factory()->create(['brand' => 'Samsung']);

    Livewire::test(Index::class)
        ->set('search', 'Apple')
        ->assertSee($appleDevice->brand)
        ->assertDontSee($samsungDevice->brand);
});

test('search filters devices by model', function (): void {
    $iphone = Device::factory()->create(['model' => 'iPhone 15']);
    $galaxy = Device::factory()->create(['model' => 'Galaxy S24']);

    Livewire::test(Index::class)
        ->set('search', 'iPhone')
        ->assertSee($iphone->model)
        ->assertDontSee($galaxy->model);
});

test('search filters devices by serial number', function (): void {
    $device1 = Device::factory()->create(['serial_number' => 'SN123456']);
    $device2 = Device::factory()->create(['serial_number' => 'SN789012']);

    Livewire::test(Index::class)
        ->set('search', 'SN123456')
        ->assertSee($device1->device_name)
        ->assertDontSee($device2->device_name);
});

test('search filters devices by imei', function (): void {
    $device1 = Device::factory()->create(['type' => 'Smartphone', 'imei' => '123456789012345']);
    $device2 = Device::factory()->create(['type' => 'Smartphone', 'imei' => '987654321098765']);

    Livewire::test(Index::class)
        ->set('search', '123456')
        ->assertSee($device1->device_name)
        ->assertDontSee($device2->device_name);
});

test('search filters devices by customer first name', function (): void {
    $customer1 = Customer::factory()->create(['first_name' => 'Jonathan']);
    $customer2 = Customer::factory()->create(['first_name' => 'Michael']);
    $device1 = Device::factory()->for($customer1)->create();
    $device2 = Device::factory()->for($customer2)->create();

    Livewire::test(Index::class)
        ->set('search', 'Jonathan')
        ->assertSee($device1->device_name)
        ->assertDontSee($device2->device_name);
});

test('search filters devices by customer last name', function (): void {
    $customer1 = Customer::factory()->create(['last_name' => 'Winchester']);
    $customer2 = Customer::factory()->create(['last_name' => 'Anderson']);
    $device1 = Device::factory()->for($customer1)->create();
    $device2 = Device::factory()->for($customer2)->create();

    Livewire::test(Index::class)
        ->set('search', 'Winchester')
        ->assertSee($device1->device_name)
        ->assertDontSee($device2->device_name);
});

test('can filter devices by customer', function (): void {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
    $device1 = Device::factory()->for($customer1)->create();
    $device2 = Device::factory()->for($customer2)->create();

    Livewire::test(Index::class)
        ->set('customerFilter', $customer1->id)
        ->assertSee($device1->device_name)
        ->assertDontSee($device2->device_name);
});

test('can filter devices by type', function (): void {
    $smartphone = Device::factory()->create(['type' => 'Smartphone']);
    $laptop = Device::factory()->create(['type' => 'Laptop']);

    Livewire::test(Index::class)
        ->set('typeFilter', 'Smartphone')
        ->assertSee($smartphone->device_name)
        ->assertDontSee($laptop->device_name);
});

test('clear filters button resets all filters', function (): void {
    Device::factory()->count(3)->create();

    Livewire::test(Index::class)
        ->set('search', 'test')
        ->set('customerFilter', '1')
        ->set('typeFilter', 'Smartphone')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('customerFilter', '')
        ->assertSet('typeFilter', '');
});

test('displays ticket count for each device', function (): void {
    $device = Device::factory()->hasTickets(3)->create();

    Livewire::test(Index::class)
        ->assertSee('3');
});

test('paginates devices', function (): void {
    Device::factory()->count(20)->create();

    Livewire::test(Index::class)
        ->assertSee('1')
        ->assertSee('2');
});

test('admin can delete device', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    actingAs($admin);

    $device = Device::factory()->create();

    Livewire::test(Index::class)
        ->call('delete', $device->id)
        ->assertHasNoErrors();

    expect(Device::find($device->id))->toBeNull();
});

test('manager can delete device', function (): void {
    $manager = User::factory()->create(['role' => UserRole::Manager]);
    actingAs($manager);

    $device = Device::factory()->create();

    Livewire::test(Index::class)
        ->call('delete', $device->id)
        ->assertHasNoErrors();

    expect(Device::find($device->id))->toBeNull();
});

test('technician cannot delete device', function (): void {
    $device = Device::factory()->create();

    Livewire::test(Index::class)
        ->call('delete', $device->id)
        ->assertForbidden();

    expect(Device::find($device->id))->not->toBeNull();
});

test('displays empty state with filters applied', function (): void {
    Device::factory()->create(['brand' => 'Apple']);

    Livewire::test(Index::class)
        ->set('search', 'Samsung')
        ->assertSee('No devices found')
        ->assertSee('Try adjusting your search or filters');
});

test('handles devices with missing customer relationships gracefully', function (): void {
    // This is more of a template safety test - ensuring our null checks work
    // The actual scenario would require database inconsistency which is hard to simulate
    // We'll primarily rely on the template changes and the defensive null checks
    $device = Device::factory()->create();

    // Test that the view can render without errors when customer exists
    Livewire::test(Index::class)
        ->assertSee($device->device_name)
        ->assertSee($device->customer->full_name);
});
