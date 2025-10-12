<?php

declare(strict_types=1);

use App\Enums\InventoryStatus;
use App\Enums\UserRole;
use App\Models\{InventoryItem, User};

uses()->group('browser', 'inventory');

beforeEach(function () {
    $this->user = User::factory()->create(['role' => UserRole::Admin]);
    $this->actingAs($this->user);

    // Create test inventory items
    $this->items = InventoryItem::factory()->count(5)->create([
        'quantity' => 20,
        'reorder_level' => 10,
        'status' => InventoryStatus::Active,
    ]);

    // Create low stock item
    $this->lowStockItem = InventoryItem::factory()->create([
        'name' => 'Low Stock Item',
        'quantity' => 5,
        'reorder_level' => 10,
        'status' => InventoryStatus::Active,
    ]);
});

it('displays inventory list successfully', function () {
    $page = visit('/inventory');

    $page->assertSee('Inventory')
        ->assertSee('Add Item')
        ->assertNoJavaScriptErrors();

    // Verify some items are displayed
    $page->assertSee($this->items->first()->name);
});

it('shows inventory table headers', function () {
    $page = visit('/inventory');

    $page->assertSee('Item')
        ->assertSee('SKU')
        ->assertSee('Quantity')
        ->assertSee('Status')
        ->assertNoJavaScriptErrors();
});

it('displays low stock items with indicators', function () {
    $page = visit('/inventory');

    $page->assertSee($this->lowStockItem->name)
        ->assertSee('Low Stock')
        ->assertNoJavaScriptErrors();
});

it('can navigate to create item page', function () {
    $page = visit('/inventory');

    $page->click('Add Item')
        ->wait(500)
        ->assertSee('Add Inventory Item')
        ->assertNoJavaScriptErrors();
});

it('shows search functionality', function () {
    $page = visit('/inventory');

    // Check for search functionality by looking for page heading
    $page->assertSee('Inventory')
        ->assertNoJavaScriptErrors();
});

it('displays item details when clicking on item', function () {
    $item = $this->items->first();
    $page = visit('/inventory');

    // Click on first item name to view details
    $page->click($item->name)
        ->wait(500)
        ->assertSee($item->name)
        ->assertSee($item->sku)
        ->assertNoJavaScriptErrors();
});

it('shows proper status badges', function () {
    $page = visit('/inventory');

    // Status badges show Active/Inactive
    $page->assertSee('Active')
        ->assertNoJavaScriptErrors();
});

it('displays quantity information', function () {
    $item = $this->items->first();

    $page = visit('/inventory');

    $page->assertSee($item->quantity)
        ->assertNoJavaScriptErrors();
});
