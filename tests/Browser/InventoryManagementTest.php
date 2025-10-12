<?php

declare(strict_types=1);

use App\Enums\InventoryStatus;
use App\Models\{InventoryItem, User};

uses()->group('browser');

beforeEach(function () {
    $this->user = User::factory()->create();

    // Create test inventory items
    $this->items = InventoryItem::factory()->count(5)->create([
        'quantity' => 20,
        'reorder_level' => 10,
        'status' => InventoryStatus::InStock,
    ]);

    // Create low stock item
    $this->lowStockItem = InventoryItem::factory()->create([
        'name' => 'Low Stock Item',
        'quantity' => 5,
        'reorder_level' => 10,
        'status' => InventoryStatus::LowStock,
    ]);
});

it('displays inventory list successfully', function () {
    $page = visit('/inventory')->actingAs($this->user);

    $page->assertSee('Inventory Management')
        ->assertSee('Add Item')
        ->assertSee('Search')
        ->assertNoJavaScriptErrors();

    // Verify items are displayed
    foreach ($this->items->take(3) as $item) {
        $page->assertSee($item->name);
    }
});

it('creates a new inventory item', function () {
    $page = visit('/inventory')->actingAs($this->user);

    $page->click('Add Item')
        ->waitFor('@inventory-form')
        ->type('@item-name', 'New Test Item')
        ->type('@item-sku', 'NEW-TEST-001')
        ->type('@item-price', '50.00')
        ->type('@item-cost', '30.00')
        ->type('@item-quantity', '100')
        ->type('@item-reorder-level', '20')
        ->select('@item-status', 'in_stock')
        ->click('@submit-item')
        ->assertSee('Item created successfully')
        ->assertNoJavaScriptErrors();

    // Verify item was created
    expect(InventoryItem::where('sku', 'NEW-TEST-001')->exists())->toBeTrue();

    $item = InventoryItem::where('sku', 'NEW-TEST-001')->first();
    expect($item->name)->toBe('New Test Item')
        ->and($item->price)->toBe(50.0)
        ->and($item->quantity)->toBe(100);
});

it('edits an existing inventory item', function () {
    $item = $this->items->first();

    $page = visit('/inventory')->actingAs($this->user);

    $page->click('@edit-item-' . $item->id)
        ->waitFor('@inventory-form')
        ->clear('@item-name')
        ->type('@item-name', 'Updated Item Name')
        ->clear('@item-price')
        ->type('@item-price', '75.00')
        ->click('@submit-item')
        ->assertSee('Item updated successfully')
        ->assertSee('Updated Item Name')
        ->assertNoJavaScriptErrors();

    // Verify item was updated
    $item->refresh();
    expect($item->name)->toBe('Updated Item Name')
        ->and($item->price)->toBe(75.0);
});

it('adjusts inventory quantity', function () {
    $item = $this->items->first();
    $originalQuantity = $item->quantity;

    $page = visit('/inventory')->actingAs($this->user);

    $page->click('@adjust-quantity-' . $item->id)
        ->waitFor('@adjustment-form')
        ->type('@adjustment-quantity', '10')
        ->select('@adjustment-type', 'increase')
        ->type('@adjustment-reason', 'Restocked from supplier')
        ->click('@submit-adjustment')
        ->assertSee('Quantity adjusted successfully')
        ->assertNoJavaScriptErrors();

    // Verify quantity was adjusted
    $item->refresh();
    expect($item->quantity)->toBe($originalQuantity + 10);
});

it('filters inventory by low stock', function () {
    $page = visit('/inventory')->actingAs($this->user);

    $page->select('@status-filter', 'low_stock')
        ->waitFor('@inventory-list')
        ->assertSee('Low Stock Item')
        ->assertNoJavaScriptErrors();

    // Should only show low stock items
    $visibleItems = $page->elements('.inventory-item');
    expect(count($visibleItems))->toBeLessThan(count($this->items) + 1);
});

it('searches inventory items', function () {
    $item = $this->items->first();

    $page = visit('/inventory')->actingAs($this->user);

    $page->type('@search-input', $item->name)
        ->waitFor('@inventory-list')
        ->assertSee($item->name)
        ->assertNoJavaScriptErrors();
});

it('displays low stock alert badge', function () {
    $page = visit('/inventory')->actingAs($this->user);

    $page->assertSee('Low Stock Alert')
        ->assertVisible('@low-stock-badge')
        ->assertNoJavaScriptErrors();

    // Click on badge to filter
    $page->click('@low-stock-badge')
        ->waitFor('@inventory-list')
        ->assertSee($this->lowStockItem->name);
});

it('validates required fields when creating item', function () {
    $page = visit('/inventory')->actingAs($this->user);

    $page->click('Add Item')
        ->waitFor('@inventory-form')
        ->click('@submit-item')
        ->assertSee('The name field is required')
        ->assertSee('The sku field is required')
        ->assertSee('The price field is required')
        ->assertNoJavaScriptErrors();

    // Verify item was NOT created
    expect(InventoryItem::count())->toBe(count($this->items) + 1); // +1 for lowStockItem
});

it('prevents duplicate SKUs', function () {
    $existingItem = $this->items->first();

    $page = visit('/inventory')->actingAs($this->user);

    $page->click('Add Item')
        ->waitFor('@inventory-form')
        ->type('@item-name', 'Duplicate SKU Item')
        ->type('@item-sku', $existingItem->sku) // Use existing SKU
        ->type('@item-price', '50.00')
        ->type('@item-quantity', '10')
        ->click('@submit-item')
        ->assertSee('The sku has already been taken')
        ->assertNoJavaScriptErrors();
});

it('deletes an inventory item', function () {
    $item = $this->items->first();

    $page = visit('/inventory')->actingAs($this->user);

    $page->click('@delete-item-' . $item->id)
        ->waitFor('@confirm-dialog')
        ->click('@confirm-delete')
        ->assertSee('Item deleted successfully')
        ->assertDontSee($item->name)
        ->assertNoJavaScriptErrors();

    // Verify item was deleted
    expect(InventoryItem::find($item->id))->toBeNull();
});

it('sorts inventory by different columns', function () {
    $page = visit('/inventory')->actingAs($this->user);

    // Sort by name
    $page->click('@sort-name')
        ->waitFor('@inventory-list')
        ->assertNoJavaScriptErrors();

    // Sort by price
    $page->click('@sort-price')
        ->waitFor('@inventory-list')
        ->assertNoJavaScriptErrors();

    // Sort by quantity
    $page->click('@sort-quantity')
        ->waitFor('@inventory-list')
        ->assertNoJavaScriptErrors();
});

it('displays inventory adjustment history', function () {
    $item = $this->items->first();

    // Create adjustment history
    \App\Models\InventoryAdjustment::factory()->count(3)->create([
        'inventory_item_id' => $item->id,
    ]);

    $page = visit('/inventory')->actingAs($this->user);

    $page->click('@view-item-' . $item->id)
        ->waitFor('@item-details')
        ->click('@history-tab')
        ->assertSee('Adjustment History')
        ->assertCount('.adjustment-record', 3)
        ->assertNoJavaScriptErrors();
});
