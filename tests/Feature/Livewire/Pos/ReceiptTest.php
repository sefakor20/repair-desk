<?php

declare(strict_types=1);

use App\Livewire\Pos\Receipt;
use App\Models\{PosSale, User};
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('renders successfully', function () {
    $user = User::factory()->admin()->create();
    $sale = PosSale::factory()->create();

    actingAs($user);

    Livewire::test(Receipt::class, ['sale' => $sale])
        ->assertStatus(200)
        ->assertSee($sale->sale_number);
});

it('displays sale details correctly', function () {
    $user = User::factory()->admin()->create();
    $customer = \App\Models\Customer::factory()->create();
    $sale = PosSale::factory()->create([
        'customer_id' => $customer->id,
        'total_amount' => 100.50,
    ]);

    actingAs($user);

    Livewire::test(Receipt::class, ['sale' => $sale])
        ->assertSee($sale->sale_number)
        ->assertSee($customer->full_name)
        ->assertSee('GHS', false)
        ->assertSee('100.50');
});

it('displays sale items', function () {
    $user = User::factory()->admin()->create();
    $sale = PosSale::factory()->create();
    $item = \App\Models\InventoryItem::factory()->create(['name' => 'Test Product']);
    \App\Models\PosSaleItem::factory()->create([
        'pos_sale_id' => $sale->id,
        'inventory_item_id' => $item->id,
        'quantity' => 2,
        'unit_price' => 25.00,
    ]);

    actingAs($user);

    Livewire::test(Receipt::class, ['sale' => $sale])
        ->assertSee('Test Product')
        ->assertSee('2')
        ->assertSee('GHS', false)
        ->assertSee('25.00');
});

it('displays payment information', function () {
    $user = User::factory()->admin()->create();
    $sale = PosSale::factory()->create([
        'payment_method' => 'card',
        'payment_status' => 'completed',
        'payment_reference' => 'PS_123456',
    ]);

    actingAs($user);

    Livewire::test(Receipt::class, ['sale' => $sale])
        ->assertSee('Card')
        ->assertSee('PS_123456')
        ->assertSee('completed', false); // Using false to match uppercase in view
});
