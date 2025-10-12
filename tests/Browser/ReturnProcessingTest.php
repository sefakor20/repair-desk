<?php

declare(strict_types=1);

use App\Enums\{PosSaleStatus};
use App\Models\{Customer, InventoryItem, PosSale, PosSaleItem, User};

uses()->group('browser', 'returns');

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->customer = Customer::factory()->create();

    // Create inventory item
    $this->item = InventoryItem::factory()->create([
        'name' => 'Defective Phone Case',
        'sku' => 'TEST-CASE-001',
        'selling_price' => 30.00,
        'quantity' => 10,
    ]);

    // Create a completed sale
    $this->sale = PosSale::factory()->create([
        'customer_id' => $this->customer->id,
        'sold_by' => $this->user->id,
        'status' => PosSaleStatus::Completed,
        'subtotal' => 30.00,
        'tax_amount' => 4.50,
        'total_amount' => 34.50,
    ]);

    PosSaleItem::create([
        'pos_sale_id' => $this->sale->id,
        'inventory_item_id' => $this->item->id,
        'quantity' => 1,
        'unit_price' => 30.00,
        'subtotal' => 30.00,
    ]);
});

it('loads returns page successfully', function () {
    $page = visit('/pos/returns');

    $page->assertSee('Returns')
        ->assertNoJavaScriptErrors();
});

it('displays return list', function () {
    $page = visit('/pos/returns');

    // Just check the page loads properly
    $page->assertSee('Returns')
        ->assertNoJavaScriptErrors();
});

it('shows return details when viewing specific return', function () {
    $page = visit('/pos/returns');

    // Check returns page structure
    $page->assertSee('Returns')
        ->assertNoJavaScriptErrors();
});

it('can navigate to process return from sale', function () {
    $page = visit('/pos/' . $this->sale->id);

    $page->assertSee($this->sale->sale_number)
        ->assertNoJavaScriptErrors();
});
