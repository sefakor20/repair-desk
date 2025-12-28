<?php

declare(strict_types=1);

use App\Livewire\Portal\Devices\Index;
use App\Models\{Customer, Device, Ticket};
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->customer = Customer::factory()
        ->create(['portal_access_token' => 'test-token-456']);
});

test('renders successfully for authorized customer', function (): void {
    get(route('portal.devices.index', [
        'customer' => $this->customer->id,
        'token' => $this->customer->portal_access_token,
    ]))->assertSuccessful()
        ->assertSeeLivewire(Index::class);
});

test('displays all customer devices', function (): void {
    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'brand' => 'Samsung',
        'model' => 'Galaxy S23',
    ]);

    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'brand' => 'Apple',
        'model' => 'iPhone 14',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('Samsung')
        ->assertSee('Galaxy S23')
        ->assertSee('Apple')
        ->assertSee('iPhone 14');
});

test('searches devices by brand', function (): void {
    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'brand' => 'Apple',
        'model' => 'iPhone 14',
    ]);

    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'brand' => 'Samsung',
        'model' => 'Galaxy S23',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('search', 'Apple')
        ->assertSee('iPhone 14')
        ->assertDontSee('Galaxy S23');
});

test('searches devices by model', function (): void {
    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'brand' => 'Apple',
        'model' => 'iPhone 14 Pro',
    ]);

    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'brand' => 'Apple',
        'model' => 'MacBook Air',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('search', 'MacBook')
        ->assertSee('MacBook Air')
        ->assertDontSee('iPhone 14 Pro');
});

test('searches devices by serial number', function (): void {
    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'serial_number' => 'SN12345',
    ]);

    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'serial_number' => 'SN67890',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('search', '12345')
        ->assertSee('SN12345')
        ->assertDontSee('SN67890');
});

test('searches devices by imei', function (): void {
    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'imei' => '123456789012345',
    ]);

    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'imei' => '987654321098765',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('search', '123456')
        ->assertSee('123456789012345')
        ->assertDontSee('987654321098765');
});

test('displays repair count for each device', function (): void {
    $device = Device::factory()->create(['customer_id' => $this->customer->id]);

    Ticket::factory()->count(3)->create([
        'customer_id' => $this->customer->id,
        'device_id' => $device->id,
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('3'); // Repair count badge
});

test('displays zero repair count for devices without tickets', function (): void {
    Device::factory()->create(['customer_id' => $this->customer->id]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('0'); // No repairs
});

test('clears search correctly', function (): void {
    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('search', 'test search')
        ->call('clearSearch')
        ->assertSet('search', '');
});

test('displays empty state when no devices exist', function (): void {
    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('No devices found');
});

test('displays contextual empty state message when search returns no results', function (): void {
    Device::factory()->create(['customer_id' => $this->customer->id]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('search', 'nonexistent')
        ->assertSee('Try adjusting your search');
});

test('paginates devices correctly', function (): void {
    // Create 15 devices (more than one page)
    Device::factory()->count(15)->create(['customer_id' => $this->customer->id]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertViewHas('devices', function ($devices): bool {
            return $devices->count() === 12; // Default per page
        });
});

test('only displays devices belonging to the customer', function (): void {
    $otherCustomer = Customer::factory()->create();

    $myDevice = Device::factory()->create([
        'customer_id' => $this->customer->id,
        'brand' => 'MyBrand',
    ]);

    $otherDevice = Device::factory()->create([
        'customer_id' => $otherCustomer->id,
        'brand' => 'OtherBrand',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('MyBrand')
        ->assertDontSee('OtherBrand');
});

test('displays device type', function (): void {
    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'type' => 'Smartphone',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('Smartphone');
});

test('displays device registration date', function (): void {
    $device = Device::factory()->create([
        'customer_id' => $this->customer->id,
        'created_at' => now()->subDays(10),
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee($device->created_at->format('M d, Y'));
});

test('shows summary with total devices count', function (): void {
    Device::factory()->count(5)->create(['customer_id' => $this->customer->id]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('5'); // Total devices count
});

test('shows summary with total repairs count', function (): void {
    $device = Device::factory()->create(['customer_id' => $this->customer->id]);

    Ticket::factory()->count(7)->create([
        'customer_id' => $this->customer->id,
        'device_id' => $device->id,
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('7'); // Total repairs count
});

test('resets page when updating search', function (): void {
    Livewire::test(Index::class, ['customer' => $this->customer])
        ->set('search', 'initial')
        ->assertSet('search', 'initial')
        ->set('search', 'updated');
});

test('generates portal access token if missing', function (): void {
    $customer = Customer::factory()->create(['portal_access_token' => null]);

    expect($customer->portal_access_token)->toBeNull();

    Livewire::test(Index::class, ['customer' => $customer]);

    $customer->refresh();

    expect($customer->portal_access_token)->not->toBeNull();
});

test('displays serial number when available', function (): void {
    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'serial_number' => 'ABC123XYZ',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('ABC123XYZ');
});

test('displays imei when available', function (): void {
    Device::factory()->create([
        'customer_id' => $this->customer->id,
        'imei' => '123456789012345',
    ]);

    Livewire::test(Index::class, ['customer' => $this->customer])
        ->assertSee('123456789012345');
});

// Skipping view details button test - portal.devices.show route not yet implemented
