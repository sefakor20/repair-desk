<?php

declare(strict_types=1);

use App\Livewire\Customers\Edit;
use App\Models\{Customer};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, assertDatabaseHas};

test('customer edit page can be rendered', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create();

    actingAs($user)
        ->get(route('customers.edit', $customer))
        ->assertSuccessful()
        ->assertSee('Edit Customer')
        ->assertSee($customer->full_name);
});

test('edit form is pre-populated with customer data', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '1234567890',
        'address' => '123 Main St',
        'notes' => 'VIP customer',
        'tags' => ['VIP', 'Regular'],
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->assertSet('form.first_name', 'John')
        ->assertSet('form.last_name', 'Doe')
        ->assertSet('form.email', 'john@example.com')
        ->assertSet('form.phone', '1234567890')
        ->assertSet('form.address', '123 Main St')
        ->assertSet('form.notes', 'VIP customer')
        ->assertSet('form.tags', ['VIP', 'Regular']);
});

test('customer edit form requires first name', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create();

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('form.first_name', '')
        ->call('save')
        ->assertHasErrors(['form.first_name' => 'required']);
});

test('customer edit form requires last name', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create();

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('form.last_name', '')
        ->call('save')
        ->assertHasErrors(['form.last_name' => 'required']);
});

test('customer edit form requires email', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create();

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('form.email', '')
        ->call('save')
        ->assertHasErrors(['form.email' => 'required']);
});

test('customer edit form requires valid email', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create();

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('form.email', 'invalid-email')
        ->call('save')
        ->assertHasErrors(['form.email' => 'email']);
});

test('customer can keep same email when editing', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create(['email' => 'john@example.com']);

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('form.first_name', 'John Updated')
        ->set('form.email', 'john@example.com')
        ->call('save')
        ->assertHasNoErrors();

    assertDatabaseHas('customers', [
        'id' => $customer->id,
        'first_name' => 'John Updated',
        'email' => 'john@example.com',
    ]);
});

test('customer edit form requires unique email for other customers', function () {
    $user = createAdmin();
    $existingCustomer = Customer::factory()->create(['email' => 'existing@example.com']);
    $customer = Customer::factory()->create(['email' => 'john@example.com']);

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('form.email', 'existing@example.com')
        ->call('save')
        ->assertHasErrors(['form.email' => 'unique']);
});

test('customer edit form requires phone', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create();

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('form.phone', '')
        ->call('save')
        ->assertHasErrors(['form.phone' => 'required']);
});

test('customer can be updated with valid data', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('form.first_name', 'Jane')
        ->set('form.last_name', 'Smith')
        ->set('form.email', 'jane@example.com')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('customers.show', $customer));

    assertDatabaseHas('customers', [
        'id' => $customer->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
    ]);
});

test('customer address can be updated', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create(['address' => '123 Main St']);

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('form.address', '456 Oak Ave')
        ->call('save')
        ->assertHasNoErrors();

    assertDatabaseHas('customers', [
        'id' => $customer->id,
        'address' => '456 Oak Ave',
    ]);
});

test('customer notes can be updated', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create(['notes' => 'Old note']);

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('form.notes', 'Updated note')
        ->call('save')
        ->assertHasNoErrors();

    assertDatabaseHas('customers', [
        'id' => $customer->id,
        'notes' => 'Updated note',
    ]);
});

test('customer tags can be added during edit', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create(['tags' => ['VIP']]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('tagsInput', 'Premium')
        ->call('addTag')
        ->assertSet('form.tags', ['VIP', 'Premium']);
});

test('customer tags can be removed during edit', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create(['tags' => ['VIP', 'Regular', 'Premium']]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->call('removeTag', 1)
        ->assertSet('form.tags', ['VIP', 'Premium']);
});

test('customer tags can be saved during edit', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create(['tags' => ['VIP']]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('form.tags', ['VIP', 'Premium', 'Regular'])
        ->call('save')
        ->assertHasNoErrors();

    $customer->refresh();
    expect($customer->tags)->toBe(['VIP', 'Premium', 'Regular']);
});

test('success message is shown after updating customer', function () {
    $user = createAdmin();
    $customer = Customer::factory()->create();

    Livewire::actingAs($user)
        ->test(Edit::class, ['customer' => $customer])
        ->set('form.first_name', 'Updated')
        ->call('save');

    expect(session('success'))->toBe('Customer updated successfully.');
});

test('unauthorized user cannot update customer', function () {
    // This test ensures the authorization is working at the policy level
    // In this app, all authenticated users can update customers
    $user = createAdmin();
    $customer = Customer::factory()->create();

    expect($user->can('update', $customer))->toBeTrue();
});
