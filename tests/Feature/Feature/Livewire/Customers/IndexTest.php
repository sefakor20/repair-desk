<?php

declare(strict_types=1);

use App\Livewire\Customers\Index;
use App\Models\Customer;
use App\Models\User;
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

test('search filters customers', function () {
    $user = User::factory()->create();
    $john = Customer::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
    $jane = Customer::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

test('customers are paginated', function () {
    $user = User::factory()->create();
    Customer::factory()->count(20)->create();

    $response = actingAs($user)->get(route('customers.index'));

    // Should paginate at 15 per page, so we should see pagination links
    $response->assertSee('Next');
});
