<?php

declare(strict_types=1);

use App\Enums\{PaymentMethod, PosSaleStatus};
use App\Livewire\Pos\Index;
use App\Models\{Customer, PosSale, PosSaleItem};
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, get};

test('pos index page can be rendered', function () {
    actingAs(createAdmin());

    get(route('pos.index'))
        ->assertSuccessful();
});

test('unauthorized user cannot access pos index page', function () {
    get(route('pos.index'))
        ->assertRedirect(route('login'));
});

test('pos index page displays sales', function () {
    $user = createAdmin();
    actingAs($user);

    $sale = PosSale::factory()->create([
        'sold_by' => $user->id,
    ]);

    PosSaleItem::factory()->create([
        'pos_sale_id' => $sale->id,
    ]);

    Livewire::test(Index::class)
        ->assertSee($sale->sale_number)
        ->assertSee(number_format((float) $sale->total_amount, 2));
});

test('pos index shows empty state when no sales exist', function () {
    actingAs(createAdmin());

    Livewire::test(Index::class)
        ->assertSee('No sales yet');
});

test('can search sales by sale number', function () {
    $user = createAdmin();
    actingAs($user);

    $sale1 = PosSale::factory()->create([
        'sale_number' => 'POS-12345',
        'sold_by' => $user->id,
    ]);
    $sale2 = PosSale::factory()->create([
        'sale_number' => 'POS-67890',
        'sold_by' => $user->id,
    ]);

    Livewire::test(Index::class)
        ->set('searchTerm', 'POS-12345')
        ->assertSee($sale1->sale_number)
        ->assertDontSee($sale2->sale_number);
});

test('can search sales by customer name', function () {
    $user = createAdmin();
    actingAs($user);

    $customer1 = Customer::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
    $customer2 = Customer::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

    $sale1 = PosSale::factory()->create([
        'customer_id' => $customer1->id,
        'sold_by' => $user->id,
    ]);
    $sale2 = PosSale::factory()->create([
        'customer_id' => $customer2->id,
        'sold_by' => $user->id,
    ]);

    Livewire::test(Index::class)
        ->set('searchTerm', 'John')
        ->assertSee($sale1->sale_number)
        ->assertDontSee($sale2->sale_number);
});

test('can filter sales by status', function () {
    $user = createAdmin();
    actingAs($user);

    $completedSale = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sold_by' => $user->id,
    ]);
    $refundedSale = PosSale::factory()->create([
        'status' => PosSaleStatus::Refunded,
        'sold_by' => $user->id,
    ]);

    Livewire::test(Index::class)
        ->set('statusFilter', 'completed')
        ->assertSee($completedSale->sale_number)
        ->assertDontSee($refundedSale->sale_number);
});

test('can filter sales by payment method', function () {
    $user = createAdmin();
    actingAs($user);

    $cashSale = PosSale::factory()->create([
        'payment_method' => PaymentMethod::Cash,
        'sold_by' => $user->id,
    ]);
    $cardSale = PosSale::factory()->create([
        'payment_method' => PaymentMethod::Card,
        'sold_by' => $user->id,
    ]);

    Livewire::test(Index::class)
        ->set('paymentMethodFilter', 'cash')
        ->assertSee($cashSale->sale_number)
        ->assertDontSee($cardSale->sale_number);
});

test('can clear all filters', function () {
    $user = createAdmin();
    actingAs($user);

    Livewire::test(Index::class)
        ->set('searchTerm', 'test')
        ->set('statusFilter', 'completed')
        ->set('paymentMethodFilter', 'cash')
        ->call('clearFilters')
        ->assertSet('searchTerm', '')
        ->assertSet('statusFilter', '')
        ->assertSet('paymentMethodFilter', '');
});

test('sales are paginated', function () {
    $user = createAdmin();
    actingAs($user);

    PosSale::factory(20)->create([
        'sold_by' => $user->id,
    ]);

    Livewire::test(Index::class)
        ->assertSee('1')
        ->assertSee('2');
});

test('displays walk-in customer for sales without customer', function () {
    $user = createAdmin();
    actingAs($user);

    $sale = PosSale::factory()->create([
        'customer_id' => null,
        'sold_by' => $user->id,
    ]);

    Livewire::test(Index::class)
        ->assertSee('Walk-in Customer');
});
