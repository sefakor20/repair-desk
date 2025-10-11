<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Devices\Create;
use App\Models\{Customer, Device, User};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

beforeEach(function () {
    $this->user = User::factory()->create(['role' => UserRole::Technician]);
    actingAs($this->user);
});

test('device create page can be rendered', function () {
    get(route('devices.create'))
        ->assertOk()
        ->assertSeeLivewire(Create::class);
});

test('device create requires authentication', function () {
    auth()->logout();

    get(route('devices.create'))
        ->assertRedirect(route('login'));
});

test('displays customer dropdown', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->assertSee($customer->full_name);
});

test('can create device with required fields', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', 'Smartphone')
        ->set('form.brand', 'Apple')
        ->set('form.model', 'iPhone 15 Pro')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect(Device::count())->toBe(1);

    $device = Device::first();
    expect($device->customer_id)->toBe($customer->id);
    expect($device->type)->toBe('Smartphone');
    expect($device->brand)->toBe('Apple');
    expect($device->model)->toBe('iPhone 15 Pro');
});

test('can create device with all fields', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', 'Smartphone')
        ->set('form.brand', 'Apple')
        ->set('form.model', 'iPhone 15 Pro')
        ->set('form.serial_number', 'SN123456')
        ->set('form.imei', '123456789012345')
        ->set('form.notes', 'Test notes')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $device = Device::first();
    expect($device->serial_number)->toBe('SN123456');
    expect($device->imei)->toBe('123456789012345');
    expect($device->notes)->toBe('Test notes');
});

test('customer_id is required', function () {
    Livewire::test(Create::class)
        ->set('form.type', 'Smartphone')
        ->set('form.brand', 'Apple')
        ->set('form.model', 'iPhone 15 Pro')
        ->call('save')
        ->assertHasErrors(['form.customer_id' => 'required']);

    expect(Device::count())->toBe(0);
});

test('customer_id must exist', function () {
    Livewire::test(Create::class)
        ->set('form.customer_id', '999')
        ->set('form.type', 'Smartphone')
        ->set('form.brand', 'Apple')
        ->set('form.model', 'iPhone 15 Pro')
        ->call('save')
        ->assertHasErrors(['form.customer_id']);

    expect(Device::count())->toBe(0);
});

test('type is required', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.brand', 'Apple')
        ->set('form.model', 'iPhone 15 Pro')
        ->call('save')
        ->assertHasErrors(['form.type' => 'required']);

    expect(Device::count())->toBe(0);
});

test('brand is required', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', 'Smartphone')
        ->set('form.model', 'iPhone 15 Pro')
        ->call('save')
        ->assertHasErrors(['form.brand' => 'required']);

    expect(Device::count())->toBe(0);
});

test('model is required', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', 'Smartphone')
        ->set('form.brand', 'Apple')
        ->call('save')
        ->assertHasErrors(['form.model' => 'required']);

    expect(Device::count())->toBe(0);
});

test('serial_number is optional', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', 'Smartphone')
        ->set('form.brand', 'Apple')
        ->set('form.model', 'iPhone 15 Pro')
        ->call('save')
        ->assertHasNoErrors();

    expect(Device::count())->toBe(1);
});

test('imei is optional', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', 'Smartphone')
        ->set('form.brand', 'Apple')
        ->set('form.model', 'iPhone 15 Pro')
        ->call('save')
        ->assertHasNoErrors();

    expect(Device::count())->toBe(1);
});

test('notes is optional', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', 'Smartphone')
        ->set('form.brand', 'Apple')
        ->set('form.model', 'iPhone 15 Pro')
        ->call('save')
        ->assertHasNoErrors();

    expect(Device::count())->toBe(1);
});

test('type cannot exceed 255 characters', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', str_repeat('a', 256))
        ->set('form.brand', 'Apple')
        ->set('form.model', 'iPhone 15 Pro')
        ->call('save')
        ->assertHasErrors(['form.type']);

    expect(Device::count())->toBe(0);
});

test('brand cannot exceed 255 characters', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', 'Smartphone')
        ->set('form.brand', str_repeat('a', 256))
        ->set('form.model', 'iPhone 15 Pro')
        ->call('save')
        ->assertHasErrors(['form.brand']);

    expect(Device::count())->toBe(0);
});

test('model cannot exceed 255 characters', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', 'Smartphone')
        ->set('form.brand', 'Apple')
        ->set('form.model', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['form.model']);

    expect(Device::count())->toBe(0);
});

test('serial_number cannot exceed 255 characters', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', 'Smartphone')
        ->set('form.brand', 'Apple')
        ->set('form.model', 'iPhone 15 Pro')
        ->set('form.serial_number', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['form.serial_number']);

    expect(Device::count())->toBe(0);
});

test('imei cannot exceed 255 characters', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', 'Smartphone')
        ->set('form.brand', 'Apple')
        ->set('form.model', 'iPhone 15 Pro')
        ->set('form.imei', str_repeat('a', 256))
        ->call('save')
        ->assertHasErrors(['form.imei']);

    expect(Device::count())->toBe(0);
});

test('redirects to device show page after creation', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Create::class)
        ->set('form.customer_id', $customer->id)
        ->set('form.type', 'Smartphone')
        ->set('form.brand', 'Apple')
        ->set('form.model', 'iPhone 15 Pro')
        ->call('save')
        ->assertRedirect();

    $device = Device::first();
    expect(session('success'))->toBe('Device registered successfully.');
});
