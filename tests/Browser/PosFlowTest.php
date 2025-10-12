<?php

declare(strict_types=1);

use App\Models\{Customer, InventoryItem, User};

uses()->group('browser');

beforeEach(function () {
    // Create test user and authenticate
    $this->user = User::factory()->create();

    // Create test customer
    $this->customer = Customer::factory()->create([
        'name' => 'Test Customer',
        'email' => 'testcustomer@example.com',
        'phone' => '1234567890',
    ]);

    // Create test inventory items
    $this->item1 = InventoryItem::factory()->create([
        'name' => 'iPhone Screen Protector',
        'sku' => 'TEST-IP-SCREEN',
        'price' => 25.00,
        'quantity' => 50,
    ]);

    $this->item2 = InventoryItem::factory()->create([
        'name' => 'Samsung Battery Pack',
        'sku' => 'TEST-SAM-BAT',
        'price' => 45.00,
        'quantity' => 30,
    ]);
});

it('completes a full POS transaction successfully', function () {
    $page = visit('/pos')->actingAs($this->user);

    // Verify POS page loads correctly
    $page->assertSee('Point of Sale')
        ->assertNoJavaScriptErrors();

    // Select customer
    $page->click('Select Customer')
        ->type('@customer-search', 'Test Customer')
        ->waitFor('@customer-' . $this->customer->id)
        ->click('@customer-' . $this->customer->id)
        ->assertSee('Test Customer');

    // Add first item to cart
    $page->type('@item-search', 'iPhone Screen')
        ->waitFor('@item-' . $this->item1->id)
        ->click('@item-' . $this->item1->id)
        ->assertSee('iPhone Screen Protector')
        ->assertSee('GHS 25.00');

    // Add second item to cart
    $page->type('@item-search', 'Samsung Battery')
        ->waitFor('@item-' . $this->item2->id)
        ->click('@item-' . $this->item2->id)
        ->assertSee('Samsung Battery Pack')
        ->assertSee('GHS 45.00');

    // Verify cart total
    $page->assertSee('Total:')
        ->assertSee('GHS 70.00');

    // Complete payment
    $page->click('Checkout')
        ->waitFor('@payment-method')
        ->click('@payment-cash')
        ->type('@payment-amount', '100')
        ->click('Complete Sale')
        ->assertSee('Sale completed successfully')
        ->assertNoJavaScriptErrors();

    // Verify sale was recorded
    expect(\App\Models\PosSale::count())->toBe(1);

    $sale = \App\Models\PosSale::first();
    expect($sale->total_amount)->toBe(70.0)
        ->and($sale->customer_id)->toBe($this->customer->id)
        ->and($sale->items)->toHaveCount(2);
});

it('applies discount to POS transaction', function () {
    $page = visit('/pos')->actingAs($this->user);

    // Select customer and add item
    $page->click('Select Customer')
        ->type('@customer-search', 'Test Customer')
        ->waitFor('@customer-' . $this->customer->id)
        ->click('@customer-' . $this->customer->id);

    $page->type('@item-search', 'iPhone Screen')
        ->waitFor('@item-' . $this->item1->id)
        ->click('@item-' . $this->item1->id);

    // Apply discount
    $page->click('@apply-discount')
        ->type('@discount-percentage', '10')
        ->click('@discount-apply')
        ->assertSee('Discount: 10%')
        ->assertSee('GHS 22.50'); // 25 - 10%

    // Complete sale
    $page->click('Checkout')
        ->click('@payment-cash')
        ->type('@payment-amount', '25')
        ->click('Complete Sale')
        ->assertSee('Sale completed successfully');

    // Verify discount was applied
    $sale = \App\Models\PosSale::first();
    expect($sale->discount_amount)->toBeGreaterThan(0);
});

it('validates insufficient payment amount', function () {
    $page = visit('/pos')->actingAs($this->user);

    // Select customer and add item
    $page->click('Select Customer')
        ->type('@customer-search', 'Test Customer')
        ->waitFor('@customer-' . $this->customer->id)
        ->click('@customer-' . $this->customer->id);

    $page->type('@item-search', 'iPhone Screen')
        ->waitFor('@item-' . $this->item1->id)
        ->click('@item-' . $this->item1->id);

    // Try to complete with insufficient payment
    $page->click('Checkout')
        ->click('@payment-cash')
        ->type('@payment-amount', '10') // Less than 25
        ->click('Complete Sale')
        ->assertSee('Insufficient payment amount')
        ->assertNoJavaScriptErrors();

    // Verify sale was NOT recorded
    expect(\App\Models\PosSale::count())->toBe(0);
});

it('removes items from cart', function () {
    $page = visit('/pos')->actingAs($this->user);

    // Add items to cart
    $page->type('@item-search', 'iPhone Screen')
        ->waitFor('@item-' . $this->item1->id)
        ->click('@item-' . $this->item1->id)
        ->assertSee('iPhone Screen Protector');

    $page->type('@item-search', 'Samsung Battery')
        ->waitFor('@item-' . $this->item2->id)
        ->click('@item-' . $this->item2->id)
        ->assertSee('Samsung Battery Pack');

    // Remove first item
    $page->click('@remove-item-' . $this->item1->id)
        ->assertDontSee('iPhone Screen Protector')
        ->assertSee('Samsung Battery Pack')
        ->assertSee('GHS 45.00'); // Only second item remains
});

it('clears entire cart', function () {
    $page = visit('/pos')->actingAs($this->user);

    // Add items to cart
    $page->type('@item-search', 'iPhone Screen')
        ->waitFor('@item-' . $this->item1->id)
        ->click('@item-' . $this->item1->id);

    $page->type('@item-search', 'Samsung Battery')
        ->waitFor('@item-' . $this->item2->id)
        ->click('@item-' . $this->item2->id);

    // Clear cart
    $page->click('@clear-cart')
        ->assertSee('Cart cleared')
        ->assertDontSee('iPhone Screen Protector')
        ->assertDontSee('Samsung Battery Pack')
        ->assertSee('GHS 0.00');
});
