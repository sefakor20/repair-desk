<?php

declare(strict_types=1);

use App\Livewire\Pos\PaystackPayment;
use App\Models\{PosSale, User};
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('renders successfully for card payment sale', function () {
    $user = User::factory()->admin()->create();
    $sale = PosSale::factory()->create([
        'payment_method' => 'card',
        'payment_status' => 'pending',
    ]);

    actingAs($user);

    Livewire::test(PaystackPayment::class, ['sale' => $sale])
        ->assertStatus(200)
        ->assertSee('Complete Payment with Paystack')
        ->assertSee($sale->sale_number);
});

it('prefills email from customer', function () {
    $user = User::factory()->admin()->create();
    $customer = \App\Models\Customer::factory()->create([
        'email' => 'customer@example.com',
    ]);
    $sale = PosSale::factory()->create([
        'customer_id' => $customer->id,
        'payment_method' => 'card',
        'payment_status' => 'pending',
    ]);

    actingAs($user);

    Livewire::test(PaystackPayment::class, ['sale' => $sale])
        ->assertSet('email', 'customer@example.com');
});

it('validates email before payment initialization', function () {
    $user = User::factory()->admin()->create();
    $sale = PosSale::factory()->create([
        'payment_method' => 'card',
        'payment_status' => 'pending',
    ]);

    actingAs($user);

    Livewire::test(PaystackPayment::class, ['sale' => $sale])
        ->set('email', '')
        ->call('initializePayment')
        ->assertHasErrors('email');
});
