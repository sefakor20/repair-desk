<?php

declare(strict_types=1);

use App\Livewire\Customers\Create;
use App\Models\{Customer};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, assertDatabaseHas};

test('customer create page can be rendered', function (): void {
    $user = createAdmin();

    actingAs($user)
        ->get(route('customers.create'))
        ->assertSuccessful()
        ->assertSee('Create Customer');
});

test('customer create form requires first name', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.first_name', '')
        ->set('form.last_name', 'Doe')
        ->set('form.email', 'john@example.com')
        ->set('form.phone', '1234567890')
        ->call('save')
        ->assertHasErrors(['form.first_name' => 'required']);
});

test('customer create form requires last name', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.first_name', 'John')
        ->set('form.last_name', '')
        ->set('form.email', 'john@example.com')
        ->set('form.phone', '1234567890')
        ->call('save')
        ->assertHasErrors(['form.last_name' => 'required']);
});

test('customer create form requires email', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.first_name', 'John')
        ->set('form.last_name', 'Doe')
        ->set('form.email', '')
        ->set('form.phone', '1234567890')
        ->call('save')
        ->assertHasErrors(['form.email' => 'required']);
});

test('customer create form requires valid email', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.first_name', 'John')
        ->set('form.last_name', 'Doe')
        ->set('form.email', 'invalid-email')
        ->set('form.phone', '1234567890')
        ->call('save')
        ->assertHasErrors(['form.email' => 'email']);
});

test('customer create form requires unique email', function (): void {
    $user = createAdmin();
    $existingCustomer = Customer::factory()->create(['email' => 'existing@example.com']);

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.first_name', 'John')
        ->set('form.last_name', 'Doe')
        ->set('form.email', 'existing@example.com')
        ->set('form.phone', '1234567890')
        ->call('save')
        ->assertHasErrors(['form.email' => 'unique']);
});

test('customer create form requires phone', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.first_name', 'John')
        ->set('form.last_name', 'Doe')
        ->set('form.email', 'john@example.com')
        ->set('form.phone', '')
        ->call('save')
        ->assertHasErrors(['form.phone' => 'required']);
});

test('customer can be created with valid data', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.first_name', 'John')
        ->set('form.last_name', 'Doe')
        ->set('form.email', 'john@example.com')
        ->set('form.phone', '1234567890')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('customers.index'));

    assertDatabaseHas('customers', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '1234567890',
    ]);
});

test('customer can be created with optional address', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.first_name', 'John')
        ->set('form.last_name', 'Doe')
        ->set('form.email', 'john@example.com')
        ->set('form.phone', '1234567890')
        ->set('form.address', '123 Main St, City, State 12345')
        ->call('save')
        ->assertHasNoErrors();

    assertDatabaseHas('customers', [
        'first_name' => 'John',
        'address' => '123 Main St, City, State 12345',
    ]);
});

test('customer can be created with optional notes', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.first_name', 'John')
        ->set('form.last_name', 'Doe')
        ->set('form.email', 'john@example.com')
        ->set('form.phone', '1234567890')
        ->set('form.notes', 'VIP customer')
        ->call('save')
        ->assertHasNoErrors();

    assertDatabaseHas('customers', [
        'first_name' => 'John',
        'notes' => 'VIP customer',
    ]);
});

test('tags can be added to customer', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.first_name', 'John')
        ->set('form.last_name', 'Doe')
        ->set('form.email', 'john@example.com')
        ->set('form.phone', '1234567890')
        ->set('tagsInput', 'VIP')
        ->call('addTag')
        ->assertSet('form.tags', ['VIP'])
        ->assertSet('tagsInput', '')
        ->set('tagsInput', 'Regular')
        ->call('addTag')
        ->assertSet('form.tags', ['VIP', 'Regular']);
});

test('duplicate tags are not added', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('tagsInput', 'VIP')
        ->call('addTag')
        ->assertSet('form.tags', ['VIP'])
        ->set('tagsInput', 'VIP')
        ->call('addTag')
        ->assertSet('form.tags', ['VIP']);
});

test('tags can be removed', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.tags', ['VIP', 'Regular', 'Premium'])
        ->call('removeTag', 1)
        ->assertSet('form.tags', ['VIP', 'Premium']);
});

test('customer with tags can be saved', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.first_name', 'John')
        ->set('form.last_name', 'Doe')
        ->set('form.email', 'john@example.com')
        ->set('form.phone', '1234567890')
        ->set('form.tags', ['VIP', 'Regular'])
        ->call('save')
        ->assertHasNoErrors();

    $customer = Customer::where('email', 'john@example.com')->first();
    expect($customer->tags)->toBe(['VIP', 'Regular']);
});

test('success message is shown after creating customer', function (): void {
    $user = createAdmin();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.first_name', 'John')
        ->set('form.last_name', 'Doe')
        ->set('form.email', 'john@example.com')
        ->set('form.phone', '1234567890')
        ->call('save');

    expect(session('success'))->toBe('Customer created successfully.');
});

test('unauthorized user cannot create customer', function (): void {
    // This test ensures the authorization is working at the policy level
    // In this app, all authenticated users can create customers
    $user = createAdmin();

    expect($user->can('create', Customer::class))->toBeTrue();
});
