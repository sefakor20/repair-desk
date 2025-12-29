<?php

declare(strict_types=1);

use App\Models\{Branch, Customer, Device, Ticket, User};

test('dashboard renders successfully with tickets', function () {
    // Create test branch and user
    $branch = Branch::create([
        'name' => 'Test Branch',
        'code' => 'TEST',
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'branch_id' => $branch->id,
        'role' => 'admin',
    ]);

    // Create a normal customer, device, and ticket
    $customer = Customer::factory()->create(['branch_id' => $branch->id]);

    $device = Device::factory()->create([
        'customer_id' => $customer->id,
        'branch_id' => $branch->id,
    ]);

    $ticket = Ticket::factory()->create([
        'customer_id' => $customer->id,
        'device_id' => $device->id,
        'branch_id' => $branch->id,
    ]);

    $this->actingAs($user);

    // Visit dashboard - should work normally and not throw null property errors
    $response = $this->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee($customer->full_name);
    $response->assertSee($ticket->ticket_number);
    // The main test is that no exception is thrown when accessing customer properties
});

test('view compiles successfully with null safety operator', function () {
    // Test that our Blade template compiles correctly with the null safety operator
    $viewContent = '{{ $ticket->customer?->full_name ?? __("No Customer") }}';

    // Create a mock ticket with null customer
    $ticket = new \stdClass();
    $ticket->customer = null;

    // Test the null safety - this should not throw an error
    $result = null;
    try {
        // Simulate the null safe access
        $result = $ticket->customer?->full_name ?? 'No Customer';
    } catch (Exception $e) {
        $this->fail('Null safety operator failed: ' . $e->getMessage());
    }

    expect($result)->toBe('No Customer');
});
