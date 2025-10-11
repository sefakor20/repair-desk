<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Devices\Edit;
use App\Models\{Customer, Device, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

beforeEach(function () {
    $this->user = User::factory()->create(['role' => UserRole::Technician]);
    actingAs($this->user);
});

test('device edit page can be rendered', function () {
    $device = Device::factory()->create();

    get(route('devices.edit', $device))
        ->assertOk()
        ->assertSeeLivewire(Edit::class);
});

test('device edit requires authentication', function () {
    auth()->logout();
    $device = Device::factory()->create();

    get(route('devices.edit', $device))
        ->assertRedirect(route('login'));
});

test('form is pre-populated with device data', function () {
    $device = Device::factory()->create([
        'type' => 'Smartphone',
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro',
        'serial_number' => 'SN123456',
        'imei' => '123456789012345',
        'notes' => 'Test notes',
    ]);

    Livewire::test(Edit::class, ['device' => $device])
        ->assertSet('form.customer_id', $device->customer_id)
        ->assertSet('form.type', 'Smartphone')
        ->assertSet('form.brand', 'Apple')
        ->assertSet('form.model', 'iPhone 15 Pro')
        ->assertSet('form.serial_number', 'SN123456')
        ->assertSet('form.imei', '123456789012345')
        ->assertSet('form.notes', 'Test notes');
});

test('form handles null optional fields', function () {
    $device = Device::factory()->create([
        'serial_number' => null,
        'imei' => null,
        'notes' => null,
    ]);

    Livewire::test(Edit::class, ['device' => $device])
        ->assertSet('form.serial_number', '')
        ->assertSet('form.imei', '')
        ->assertSet('form.notes', '');
});

test('can update device with all fields', function () {
    $device = Device::factory()->create();
    $newCustomer = Customer::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.customer_id', $newCustomer->id)
        ->set('form.type', 'Laptop')
        ->set('form.brand', 'Dell')
        ->set('form.model', 'XPS 15')
        ->set('form.serial_number', 'NEW123')
        ->set('form.imei', 'NEW456')
        ->set('form.notes', 'Updated notes')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $device->refresh();
    expect($device->customer_id)->toBe($newCustomer->id);
    expect($device->type)->toBe('Laptop');
    expect($device->brand)->toBe('Dell');
    expect($device->model)->toBe('XPS 15');
    expect($device->serial_number)->toBe('NEW123');
    expect($device->imei)->toBe('NEW456');
    expect($device->notes)->toBe('Updated notes');
});

test('can update device with partial fields', function () {
    $device = Device::factory()->create([
        'brand' => 'Apple',
        'model' => 'iPhone 14',
    ]);

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.brand', 'Samsung')
        ->set('form.model', 'Galaxy S24')
        ->call('save')
        ->assertHasNoErrors();

    $device->refresh();
    expect($device->brand)->toBe('Samsung');
    expect($device->model)->toBe('Galaxy S24');
});

test('customer_id is required', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.customer_id', '')
        ->call('save')
        ->assertHasErrors(['form.customer_id' => 'required']);
});

test('customer_id must exist', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.customer_id', '999')
        ->call('save')
        ->assertHasErrors(['form.customer_id']);
});

test('type is required', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.type', '')
        ->call('save')
        ->assertHasErrors(['form.type' => 'required']);
});

test('brand is required', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.brand', '')
        ->call('save')
        ->assertHasErrors(['form.brand' => 'required']);
});

test('model is required', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.model', '')
        ->call('save')
        ->assertHasErrors(['form.model' => 'required']);
});

test('serial_number is optional', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.serial_number', '')
        ->call('save')
        ->assertHasNoErrors();

    $device->refresh();
    expect($device->serial_number)->toBeEmpty();
});

test('imei is optional', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.imei', '')
        ->call('save')
        ->assertHasNoErrors();

    $device->refresh();
    expect($device->imei)->toBeEmpty();
});

test('notes is optional', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.notes', '')
        ->call('save')
        ->assertHasNoErrors();

    $device->refresh();
    expect($device->notes)->toBeEmpty();
});

test('type cannot exceed 255 characters', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.type', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['form.type']);
});

test('brand cannot exceed 255 characters', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.brand', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['form.brand']);
});

test('model cannot exceed 255 characters', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.model', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['form.model']);
});

test('serial_number cannot exceed 255 characters', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.serial_number', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['form.serial_number']);
});

test('imei cannot exceed 255 characters', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.imei', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['form.imei']);
});

test('redirects to device show page after update', function () {
    $device = Device::factory()->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->set('form.brand', 'Updated Brand')
        ->call('save')
        ->assertRedirect();

    expect(session('success'))->toBe('Device updated successfully.');
});

test('displays customer dropdown with all customers', function () {
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();
    $device = Device::factory()->for($customer1)->create();

    Livewire::test(Edit::class, ['device' => $device])
        ->assertSee($customer1->full_name)
        ->assertSee($customer2->full_name);
});
