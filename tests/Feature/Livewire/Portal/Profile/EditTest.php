<?php

declare(strict_types=1);

use App\Livewire\Portal\Profile\Edit;
use App\Models\Customer;
use Livewire\Livewire;

use function Pest\Laravel\{assertDatabaseHas};

it('renders successfully', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Edit::class, ['customer' => $customer])
        ->assertStatus(200);
});

it('populates form with customer data on mount', function () {
    $customer = Customer::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '+1234567890',
        'address' => '123 Main St',
    ]);

    Livewire::test(Edit::class, ['customer' => $customer])
        ->assertSet('first_name', 'John')
        ->assertSet('last_name', 'Doe')
        ->assertSet('email', 'john@example.com')
        ->assertSet('phone', '+1234567890')
        ->assertSet('address', '123 Main St');
});

it('validates required fields', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Edit::class, ['customer' => $customer])
        ->set('first_name', '')
        ->set('last_name', '')
        ->set('email', '')
        ->set('phone', '')
        ->call('save')
        ->assertHasErrors(['first_name', 'last_name', 'email', 'phone']);
});

it('validates email format', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Edit::class, ['customer' => $customer])
        ->set('email', 'not-an-email')
        ->call('save')
        ->assertHasErrors(['email']);
});

it('validates email uniqueness', function () {
    $existingCustomer = Customer::factory()->create(['email' => 'existing@example.com']);
    $customer = Customer::factory()->create();

    Livewire::test(Edit::class, ['customer' => $customer])
        ->set('email', 'existing@example.com')
        ->call('save')
        ->assertHasErrors(['email']);
});

it('allows customer to keep their own email', function () {
    $customer = Customer::factory()->create(['email' => 'john@example.com']);

    Livewire::test(Edit::class, ['customer' => $customer])
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('email', 'john@example.com')
        ->set('phone', '+1234567890')
        ->call('save')
        ->assertHasNoErrors();
});

it('successfully updates customer profile', function () {
    $customer = Customer::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]);

    Livewire::test(Edit::class, ['customer' => $customer])
        ->set('first_name', 'Jane')
        ->set('last_name', 'Smith')
        ->set('email', 'jane@example.com')
        ->set('phone', '+9876543210')
        ->set('address', '456 Oak Ave')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('toast');

    assertDatabaseHas('customers', [
        'id' => $customer->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'phone' => '+9876543210',
        'address' => '456 Oak Ave',
    ]);
});

it('allows optional address field', function () {
    $customer = Customer::factory()->create();

    Livewire::test(Edit::class, ['customer' => $customer])
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('email', 'john@example.com')
        ->set('phone', '+1234567890')
        ->set('address', '')
        ->call('save')
        ->assertHasNoErrors();
});
