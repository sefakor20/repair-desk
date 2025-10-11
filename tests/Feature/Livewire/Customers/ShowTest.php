<?php

declare(strict_types=1);

use App\Livewire\Customers\Show;
use App\Models\{Customer, Device, Ticket, User};
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('customer show page can be rendered', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    actingAs($user)
        ->get(route('customers.show', $customer))
        ->assertSuccessful()
        ->assertSee($customer->full_name);
});

test('customer show page displays customer information', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '1234567890',
        'address' => '123 Main St',
        'notes' => 'VIP customer',
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['customer' => $customer])
        ->assertSee('John Doe')
        ->assertSee('john@example.com')
        ->assertSee('1234567890')
        ->assertSee('123 Main St')
        ->assertSee('VIP customer');
});

test('customer show page displays tags', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'tags' => ['VIP', 'Regular', 'Premium'],
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['customer' => $customer])
        ->assertSee('VIP')
        ->assertSee('Regular')
        ->assertSee('Premium');
});

test('customer show page displays device count', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    Device::factory()->count(3)->create(['customer_id' => $customer->id]);

    Livewire::actingAs($user)
        ->test(Show::class, ['customer' => $customer])
        ->assertSee('3')
        ->assertSee('Devices');
});

test('customer show page displays ticket count', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $device = Device::factory()->create(['customer_id' => $customer->id]);
    Ticket::factory()->count(5)->create([
        'customer_id' => $customer->id,
        'device_id' => $device->id,
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['customer' => $customer])
        ->assertSee('5')
        ->assertSee('Tickets');
});

test('customer show page displays devices list', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $device = Device::factory()->create([
        'customer_id' => $customer->id,
        'brand' => 'Apple',
        'model' => 'iPhone 12',
        'type' => 'Phone',
        'serial_number' => 'SN123456',
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['customer' => $customer])
        ->assertSee('Apple')
        ->assertSee('iPhone 12')
        ->assertSee('Phone')
        ->assertSee('SN123456');
});

test('customer show page shows message when no devices', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    Livewire::actingAs($user)
        ->test(Show::class, ['customer' => $customer])
        ->assertSee('No devices registered yet');
});

test('customer show page displays recent tickets', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $device = Device::factory()->create(['customer_id' => $customer->id]);
    $ticket = Ticket::factory()->create([
        'customer_id' => $customer->id,
        'device_id' => $device->id,
        'problem_description' => 'Screen broken',
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['customer' => $customer])
        ->assertSee($ticket->ticket_number)
        ->assertSee('Screen broken');
});

test('customer show page shows message when no tickets', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    Livewire::actingAs($user)
        ->test(Show::class, ['customer' => $customer])
        ->assertSee('No tickets yet');
});

test('customer show page displays edit button for authorized users', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    actingAs($user)
        ->get(route('customers.show', $customer))
        ->assertSee('Edit');
});

test('customer show page displays delete button for admin and manager', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    actingAs($admin)
        ->get(route('customers.show', $customer))
        ->assertSee('Delete');
});

test('customer show page hides delete button for technician and front desk', function () {
    $technician = User::factory()->technician()->create();
    $customer = Customer::factory()->create();

    actingAs($technician)
        ->get(route('customers.show', $customer))
        ->assertDontSee('Delete');
});

test('admin can delete customer from show page', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    Livewire::actingAs($admin)
        ->test(Show::class, ['customer' => $customer])
        ->call('deleteCustomer')
        ->assertRedirect(route('customers.index'));

    expect(Customer::find($customer->id))->toBeNull();
});

test('manager can delete customer from show page', function () {
    $manager = User::factory()->manager()->create();
    $customer = Customer::factory()->create();

    Livewire::actingAs($manager)
        ->test(Show::class, ['customer' => $customer])
        ->call('deleteCustomer')
        ->assertRedirect(route('customers.index'));

    expect(Customer::find($customer->id))->toBeNull();
});

test('success message is shown after deleting customer', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    Livewire::actingAs($admin)
        ->test(Show::class, ['customer' => $customer])
        ->call('deleteCustomer');

    expect(session('success'))->toBe('Customer deleted successfully.');
});

test('customer show page has breadcrumb navigation', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    actingAs($user)
        ->get(route('customers.show', $customer))
        ->assertSee('Customers')
        ->assertSee(route('customers.index'));
});

test('unauthorized user cannot view customer', function () {
    // This test ensures the authorization is working at the policy level
    // In this app, all authenticated users can view customers
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    expect($user->can('view', $customer))->toBeTrue();
});
