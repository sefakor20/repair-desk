<?php

declare(strict_types=1);

use App\Livewire\Pos\Create;
use App\Models\{InventoryItem, PosSale, Shift, User};
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('sale associates with active shift when shift is open', function (): void {
    $shift = Shift::factory()->open()->create([
        'opened_by' => $this->user->id,
    ]);

    $item = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);

    Livewire::test(Create::class)
        ->call('addToCart', $item->id)
        ->set('paymentMethod', 'cash')
        ->call('checkout')
        ->assertHasNoErrors();

    $sale = PosSale::latest()->first();
    expect($sale->shift_id)->toBe($shift->id);
});

test('sale does not associate with shift when no shift is open', function (): void {
    $item = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);

    Livewire::test(Create::class)
        ->call('addToCart', $item->id)
        ->set('paymentMethod', 'cash')
        ->call('checkout')
        ->assertHasNoErrors();

    $sale = PosSale::latest()->first();
    expect($sale->shift_id)->toBeNull();
});

test('sale does not associate with another users shift', function (): void {
    $otherUser = User::factory()->create();
    $shift = Shift::factory()->open()->create([
        'opened_by' => $otherUser->id,
    ]);

    $item = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);

    Livewire::test(Create::class)
        ->call('addToCart', $item->id)
        ->set('paymentMethod', 'cash')
        ->call('checkout')
        ->assertHasNoErrors();

    $sale = PosSale::latest()->first();
    expect($sale->shift_id)->toBeNull();
});

test('shift total sales updates when sale is created', function (): void {
    $shift = Shift::factory()->open()->create([
        'opened_by' => $this->user->id,
        'total_sales' => 0,
    ]);

    $item = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);

    Livewire::test(Create::class)
        ->call('addToCart', $item->id)
        ->set('paymentMethod', 'cash')
        ->call('checkout')
        ->assertHasNoErrors();

    $shift->refresh();
    expect($shift->total_sales)->toBe('100.00');
});

test('shift sales count increments when sale is created', function (): void {
    $shift = Shift::factory()->open()->create([
        'opened_by' => $this->user->id,
        'sales_count' => 0,
    ]);

    $item = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);

    Livewire::test(Create::class)
        ->call('addToCart', $item->id)
        ->set('paymentMethod', 'cash')
        ->call('checkout')
        ->assertHasNoErrors();

    $shift->refresh();
    expect($shift->sales_count)->toBe(1);
});

test('shift cash sales updates for cash payment', function (): void {
    $shift = Shift::factory()->open()->create([
        'opened_by' => $this->user->id,
        'cash_sales' => 0,
    ]);

    $item = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);

    Livewire::test(Create::class)
        ->call('addToCart', $item->id)
        ->set('paymentMethod', 'cash')
        ->call('checkout')
        ->assertHasNoErrors();

    $shift->refresh();
    expect($shift->cash_sales)->toBe('100.00');
});

test('shift card sales updates for card payment', function (): void {
    $shift = Shift::factory()->open()->create([
        'opened_by' => $this->user->id,
        'card_sales' => 0,
    ]);

    $item = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);

    Livewire::test(Create::class)
        ->call('addToCart', $item->id)
        ->set('paymentMethod', 'card')
        ->call('checkout')
        ->assertHasNoErrors();

    $shift->refresh();
    expect($shift->card_sales)->toBe('100.00');
});

test('shift mobile money sales updates for mobile money payment', function (): void {
    $shift = Shift::factory()->open()->create([
        'opened_by' => $this->user->id,
        'mobile_money_sales' => 0,
    ]);

    $item = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);

    Livewire::test(Create::class)
        ->call('addToCart', $item->id)
        ->set('paymentMethod', 'mobile_money')
        ->call('checkout')
        ->assertHasNoErrors();

    $shift->refresh();
    expect($shift->mobile_money_sales)->toBe('100.00');
});

test('shift bank transfer sales updates for bank transfer payment', function (): void {
    $shift = Shift::factory()->open()->create([
        'opened_by' => $this->user->id,
        'bank_transfer_sales' => 0,
    ]);

    $item = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);

    Livewire::test(Create::class)
        ->call('addToCart', $item->id)
        ->set('paymentMethod', 'bank_transfer')
        ->call('checkout')
        ->assertHasNoErrors();

    $shift->refresh();
    expect($shift->bank_transfer_sales)->toBe('100.00');
});

test('shift totals accumulate across multiple sales', function (): void {
    $shift = Shift::factory()->open()->create([
        'opened_by' => $this->user->id,
        'total_sales' => 0,
        'sales_count' => 0,
        'cash_sales' => 0,
        'card_sales' => 0,
    ]);

    $item1 = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);
    $item2 = InventoryItem::factory()->create([
        'selling_price' => 50.00,
        'quantity' => 10,
    ]);

    // First sale - cash
    Livewire::test(Create::class)
        ->call('addToCart', $item1->id)
        ->set('paymentMethod', 'cash')
        ->call('checkout')
        ->assertHasNoErrors();

    // Second sale - card
    Livewire::test(Create::class)
        ->call('addToCart', $item2->id)
        ->set('paymentMethod', 'card')
        ->call('checkout')
        ->assertHasNoErrors();

    $shift->refresh();
    expect($shift->total_sales)->toBe('150.00')
        ->and($shift->sales_count)->toBe(2)
        ->and($shift->cash_sales)->toBe('100.00')
        ->and($shift->card_sales)->toBe('50.00');
});

test('pos create page shows active shift indicator', function (): void {
    $shift = Shift::factory()->open()->create([
        'opened_by' => $this->user->id,
        'shift_name' => 'Morning Shift',
    ]);

    Livewire::test(Create::class)
        ->assertSee('Morning Shift')
        ->assertSee('Active Shift');
});

test('pos create page shows no shift warning when no shift is open', function (): void {
    Livewire::test(Create::class)
        ->assertSee('No Active Shift')
        ->assertSee('Open a shift');
});

test('shift does not update when sale is created without active shift', function (): void {
    $closedShift = Shift::factory()->closed()->create([
        'opened_by' => $this->user->id,
        'total_sales' => 100.00,
        'sales_count' => 1,
    ]);

    $item = InventoryItem::factory()->create([
        'selling_price' => 50.00,
        'quantity' => 10,
    ]);

    Livewire::test(Create::class)
        ->call('addToCart', $item->id)
        ->set('paymentMethod', 'cash')
        ->call('checkout')
        ->assertHasNoErrors();

    $closedShift->refresh();
    expect($closedShift->total_sales)->toBe('100.00')
        ->and($closedShift->sales_count)->toBe(1);
});

test('shift totals handle sales with discounts correctly', function (): void {
    $shift = Shift::factory()->open()->create([
        'opened_by' => $this->user->id,
        'total_sales' => 0,
    ]);

    $item = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);

    Livewire::test(Create::class)
        ->call('addToCart', $item->id)
        ->set('discountAmount', 10.00)
        ->set('paymentMethod', 'cash')
        ->call('checkout')
        ->assertHasNoErrors();

    $shift->refresh();
    expect($shift->total_sales)->toBe('90.00'); // 100 - 10 discount
});

test('shift totals handle sales with tax correctly', function (): void {
    // Create shop settings with tax rate
    \App\Models\ShopSettings::create([
        'shop_name' => 'Test Shop',
        'country' => 'US',
        'tax_rate' => 10,
        'currency' => 'USD',
    ]);

    $shift = Shift::factory()->open()->create([
        'opened_by' => $this->user->id,
        'total_sales' => 0,
    ]);

    $item = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);

    Livewire::test(Create::class)
        ->call('addToCart', $item->id)
        ->set('paymentMethod', 'cash')
        ->call('checkout')
        ->assertHasNoErrors();

    $shift->refresh();
    expect($shift->total_sales)->toBe('110.00'); // 100 + 10% tax
});
