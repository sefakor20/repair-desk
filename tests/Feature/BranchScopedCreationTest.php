<?php

declare(strict_types=1);

use App\Models\{Branch, Customer, Device, InventoryItem, Invoice, PosSale, Ticket, User};
use Livewire\Volt\Volt;

beforeEach(function () {
    // Create a test branch
    $this->branch = Branch::create([
        'name' => 'Test Branch',
        'code' => 'TEST',
        'is_active' => true,
    ]);

    // Create a test user in this branch
    $this->user = User::factory()->create([
        'branch_id' => $this->branch->id,
        'role' => 'admin',
    ]);

    $this->actingAs($this->user);
});

test('customer creation automatically sets branch_id', function () {
    $customerData = [
        'form.first_name' => 'John',
        'form.last_name' => 'Doe',
        'form.email' => 'john@example.com',
        'form.phone' => '1234567890',
    ];

    Volt::test('customers.create')
        ->set($customerData)
        ->call('save')
        ->assertHasNoErrors();

    $customer = Customer::where('email', 'john@example.com')->first();
    expect($customer)->not->toBeNull();
    expect($customer->branch_id)->toBe($this->branch->id);
});

test('device creation automatically sets branch_id', function () {
    $customer = Customer::factory()->create(['branch_id' => $this->branch->id]);

    $deviceData = [
        'form.customer_id' => $customer->id,
        'form.type' => 'Phone',
        'form.brand' => 'Apple',
        'form.model' => 'iPhone 13',
    ];

    Volt::test('devices.create')
        ->set($deviceData)
        ->call('save')
        ->assertHasNoErrors();

    $device = Device::where('customer_id', $customer->id)->first();
    expect($device)->not->toBeNull();
    expect($device->branch_id)->toBe($this->branch->id);
});

test('ticket creation automatically sets branch_id', function () {
    $customer = Customer::factory()->create(['branch_id' => $this->branch->id]);
    $device = Device::factory()->create([
        'customer_id' => $customer->id,
        'branch_id' => $this->branch->id,
    ]);

    $ticketData = [
        'form.customer_id' => $customer->id,
        'form.device_id' => $device->id,
        'form.problem_description' => 'Device not working properly',
        'form.priority' => 'normal',
    ];

    Volt::test('tickets.create')
        ->set($ticketData)
        ->call('save')
        ->assertHasNoErrors();

    $ticket = Ticket::where('customer_id', $customer->id)->first();
    expect($ticket)->not->toBeNull();
    expect($ticket->branch_id)->toBe($this->branch->id);
});

test('inventory item creation automatically sets branch_id', function () {
    $inventoryData = [
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'cost_price' => '10.00',
        'selling_price' => '20.00',
        'quantity' => 100,
        'reorder_level' => 10,
        'status' => 'active',
    ];

    Volt::test('inventory.create')
        ->set($inventoryData)
        ->call('save')
        ->assertHasNoErrors();

    $inventoryItem = InventoryItem::where('sku', 'TEST-001')->first();
    expect($inventoryItem)->not->toBeNull();
    expect($inventoryItem->branch_id)->toBe($this->branch->id);
});

test('pos sale creation automatically sets branch_id', function () {
    $customer = Customer::factory()->create(['branch_id' => $this->branch->id]);
    $inventoryItem = InventoryItem::factory()->create([
        'branch_id' => $this->branch->id,
        'quantity' => 10,
        'status' => 'active',
    ]);

    Volt::test('pos.create')
        ->set([
            'customerId' => $customer->id,
            'paymentMethod' => 'cash',
            'cart' => [
                $inventoryItem->id => [
                    'id' => $inventoryItem->id,
                    'name' => $inventoryItem->name,
                    'sku' => $inventoryItem->sku,
                    'unit_price' => (string) $inventoryItem->selling_price,
                    'quantity' => 1,
                    'max_quantity' => $inventoryItem->quantity,
                ],
            ],
        ])
        ->call('checkout')
        ->assertHasNoErrors();

    $sale = PosSale::where('customer_id', $customer->id)->first();
    expect($sale)->not->toBeNull();
    expect($sale->branch_id)->toBe($this->branch->id);
});

test('invoice creation automatically sets branch_id', function () {
    $customer = Customer::factory()->create(['branch_id' => $this->branch->id]);
    $device = Device::factory()->create([
        'customer_id' => $customer->id,
        'branch_id' => $this->branch->id,
    ]);
    $ticket = Ticket::factory()->create([
        'customer_id' => $customer->id,
        'device_id' => $device->id,
        'branch_id' => $this->branch->id,
    ]);

    $invoiceData = [
        'ticketId' => $ticket->id,
        'subtotal' => '100.00',
        'taxRate' => '10.00',
        'discount' => '0.00',
    ];

    Volt::test('invoices.create')
        ->set($invoiceData)
        ->call('create')
        ->assertHasNoErrors();

    $invoice = Invoice::where('ticket_id', $ticket->id)->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->branch_id)->toBe($this->branch->id);
});
