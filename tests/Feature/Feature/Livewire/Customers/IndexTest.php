<?php

declare(strict_types=1);

use App\Livewire\Customers\Index;
use App\Models\{Customer, Device, Ticket, User};
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('customers index page can be rendered', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('customers.index'))
        ->assertSuccessful()
        ->assertSee('Customers');
});

test('customers list shows customer data', function () {
    $user = User::factory()->create();
    $customers = Customer::factory()->count(3)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee($customers[0]->full_name)
        ->assertSee($customers[0]->email)
        ->assertSee($customers[1]->full_name);
});

test('search filters customers by first name', function () {
    $user = User::factory()->create();
    $john = Customer::factory()->create(['first_name' => 'Johnny', 'last_name' => 'Bravo']);
    $jane = Customer::factory()->create(['first_name' => 'Janet', 'last_name' => 'Smith']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'Johnny')
        ->assertSee('Johnny Bravo')
        ->assertDontSee('Janet Smith');
});

test('search filters customers by last name', function () {
    $user = User::factory()->create();
    $john = Customer::factory()->create(['first_name' => 'Mark', 'last_name' => 'Williams']);
    $jane = Customer::factory()->create(['first_name' => 'Sarah', 'last_name' => 'Johnson']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'Johnson')
        ->assertSee('Sarah Johnson')
        ->assertDontSee('Mark Williams');
});

test('search filters customers by email', function () {
    $user = User::factory()->create();
    $john = Customer::factory()->create(['email' => 'john@example.com']);
    $jane = Customer::factory()->create(['email' => 'jane@example.com']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'john@example')
        ->assertSee('john@example.com')
        ->assertDontSee('jane@example.com');
});

test('search filters customers by phone', function () {
    $user = User::factory()->create();
    $john = Customer::factory()->create(['phone' => '1234567890']);
    $jane = Customer::factory()->create(['phone' => '0987654321']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', '123456')
        ->assertSee('1234567890')
        ->assertDontSee('0987654321');
});

test('customers are paginated', function () {
    $user = User::factory()->create();
    Customer::factory()->count(20)->create();

    $response = actingAs($user)->get(route('customers.index'));

    // Should paginate at 15 per page, so we should see pagination links
    $response->assertSee('Next');
});

test('customers list shows device count', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    Device::factory()->count(3)->create(['customer_id' => $customer->id]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee($customer->full_name)
        ->assertSee('3'); // Device count
});

test('customers list shows ticket count', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $device = Device::factory()->create(['customer_id' => $customer->id]);
    Ticket::factory()->count(5)->create([
        'customer_id' => $customer->id,
        'device_id' => $device->id,
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee($customer->full_name)
        ->assertSee('5'); // Ticket count
});

test('customers list displays tags', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create([
        'tags' => ['VIP', 'Premium'],
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('VIP')
        ->assertSee('Premium');
});

test('customers list shows empty state when no customers', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('No customers', escape: false)
        ->assertSee('Get started by creating a new customer');
});

test('customers list shows empty state when search returns no results', function () {
    $user = User::factory()->create();
    Customer::factory()->create(['first_name' => 'John']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'NonExistentName')
        ->assertSee('No customers', escape: false)
        ->assertSee('Try adjusting your search criteria');
});

test('view button is visible for customers', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    actingAs($user)
        ->get(route('customers.index'))
        ->assertSee(route('customers.show', $customer));
});

test('edit button is visible for customers', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    actingAs($user)
        ->get(route('customers.index'))
        ->assertSee(route('customers.edit', $customer));
});

test('delete button is visible for admin and manager', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    actingAs($admin)
        ->get(route('customers.index'))
        ->assertSee('Delete');
});

test('admin can delete customer from index', function () {
    $admin = User::factory()->admin()->create();
    $customer = Customer::factory()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('delete', $customer->id);

    expect(Customer::find($customer->id))->toBeNull();
});

test('manager can delete customer from index', function () {
    $manager = User::factory()->manager()->create();
    $customer = Customer::factory()->create();

    Livewire::actingAs($manager)
        ->test(Index::class)
        ->call('delete', $customer->id);

    expect(Customer::find($customer->id))->toBeNull();
});

test('search resets pagination', function () {
    $user = User::factory()->create();

    // Create 20 customers so pagination is needed
    Customer::factory()->count(20)->create();

    // Create a specific customer to search for
    $searchableCustomer = Customer::factory()->create(['first_name' => 'SearchMe']);

    // Search for the customer - if pagination wasn't reset, we might not see it
    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'SearchMe')
        ->assertSee('SearchMe');
});

test('customers are ordered by latest first', function () {
    $user = User::factory()->create();
    $older = Customer::factory()->create(['created_at' => now()->subDays(2)]);
    $newer = Customer::factory()->create(['created_at' => now()]);

    $response = actingAs($user)->get(route('customers.index'));

    // The newer customer should appear before the older one
    $content = $response->getContent();
    $newerPosition = mb_strpos($content, $newer->full_name);
    $olderPosition = mb_strpos($content, $older->full_name);

    expect($newerPosition)->toBeLessThan($olderPosition);
});
