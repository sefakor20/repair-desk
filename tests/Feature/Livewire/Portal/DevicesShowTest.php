<?php

declare(strict_types=1);

use App\Livewire\Portal\Devices\Show;
use App\Models\{Customer, Device, Ticket, Invoice, User};
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->customer = Customer::factory()
        ->create(['portal_access_token' => 'test-token-789']);

    $this->device = Device::factory()->create([
        'customer_id' => $this->customer->id,
        'brand' => 'Apple',
        'model' => 'iPhone 14 Pro',
        'type' => 'Smartphone',
        'serial_number' => 'SN123456789',
        'imei' => '123456789012345',
    ]);
});

test('renders successfully for authorized customer', function (): void {
    get(route('portal.devices.show', [
        'customer' => $this->customer->id,
        'token' => $this->customer->portal_access_token,
        'device' => $this->device->id,
    ]))->assertSuccessful()
        ->assertSeeLivewire(Show::class);
});

test('displays device information', function (): void {
    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])
        ->assertSee('Apple')
        ->assertSee('iPhone 14 Pro')
        ->assertSee('Smartphone')
        ->assertSee('SN123456789')
        ->assertSee('123456789012345');
});

test('prevents unauthorized access to device', function (): void {
    $otherCustomer = Customer::factory()->create();
    $otherDevice = Device::factory()->create([
        'customer_id' => $otherCustomer->id,
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $otherDevice,
    ])->assertForbidden();
});

test('displays device registration date', function (): void {
    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])->assertSee($this->device->created_at->format('M d, Y'));
});

test('displays total repair count', function (): void {
    Ticket::factory()->count(5)->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])->assertSee('5');
});

// Warranty field doesn't exist in devices table - tests removed

test('displays device notes when available', function (): void {
    $this->device->update([
        'notes' => 'Customer prefers text messages for updates',
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])->assertSee('Customer prefers text messages for updates');
});

// Color and storage capacity fields don't exist in devices table - tests removed

test('displays repair history in descending order', function (): void {
    $oldTicket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
        'ticket_number' => 'TKT-001',
        'created_at' => now()->subDays(10),
    ]);

    $newTicket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
        'ticket_number' => 'TKT-002',
        'created_at' => now()->subDays(2),
    ]);

    $response = Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ]);

    $html = $response->html();
    $pos1 = mb_strpos($html, 'TKT-002');
    $pos2 = mb_strpos($html, 'TKT-001');

    expect($pos1)->toBeLessThan($pos2);
});

test('displays ticket status badge', function (): void {
    Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
        'status' => 'completed',
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])->assertSee('Completed');
});

test('displays ticket priority badge', function (): void {
    Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
        'priority' => 'high',
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])->assertSee('High Priority');
});

test('displays assigned technician', function (): void {
    $technician = User::factory()->create(['name' => 'John Smith']);

    Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
        'assigned_to' => $technician->id,
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])->assertSee('John Smith');
});

test('displays ticket created date', function (): void {
    $createdDate = now()->subDays(3);

    Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
        'status' => 'completed',
        'created_at' => $createdDate,
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])->assertSee($createdDate->format('M d, Y'));
});

test('displays invoice information for tickets', function (): void {
    $ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
    ]);

    $invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket->id,
        'invoice_number' => 'INV-TEST-001',
        'total' => 350.00,
        'status' => 'paid',
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])
        ->assertSee('INV-TEST-001')
        ->assertSee('GH₵ 350.00')
        ->assertSee('Paid');
});

test('displays invoice status badge', function (): void {
    $ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket->id,
        'status' => 'pending',
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])->assertSee('Pending');
});

test('displays balance due for unpaid invoices', function (): void {
    $ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'ticket_id' => $ticket->id,
        'total' => 500.00,
        'status' => 'pending',
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])->assertSee('Balance Due: GH₵ 500.00');
});

test('displays empty state when no repairs exist', function (): void {
    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])
        ->assertSee('No Repair History')
        ->assertSee("hasn't been serviced yet", false);
});

test('back button links to devices index', function (): void {
    $expectedRoute = route('portal.devices.index', [
        'customer' => $this->customer->id,
        'token' => $this->customer->portal_access_token,
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])->assertSee($expectedRoute);
});

test('view details button links to ticket show page', function (): void {
    $ticket = Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
    ]);

    $expectedRoute = route('portal.tickets.show', [
        'customer' => $this->customer->id,
        'token' => $this->customer->portal_access_token,
        'ticket' => $ticket->id,
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])->assertSee($expectedRoute);
});

test('generates portal access token if missing', function (): void {
    $customer = Customer::factory()->create(['portal_access_token' => null]);
    $device = Device::factory()->create(['customer_id' => $customer->id]);

    expect($customer->portal_access_token)->toBeNull();

    Livewire::test(Show::class, [
        'customer' => $customer,
        'device' => $device,
    ]);

    $customer->refresh();

    expect($customer->portal_access_token)->not->toBeNull();
});

test('displays ticket problem description', function (): void {
    Ticket::factory()->create([
        'customer_id' => $this->customer->id,
        'device_id' => $this->device->id,
        'problem_description' => 'Screen is cracked and needs replacement',
    ]);

    Livewire::test(Show::class, [
        'customer' => $this->customer,
        'device' => $this->device,
    ])->assertSee('Screen is cracked and needs replacement');
});
