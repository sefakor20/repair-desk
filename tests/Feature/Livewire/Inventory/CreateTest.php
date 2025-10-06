<?php

declare(strict_types=1);

use App\Livewire\Inventory\Create;
use App\Models\{InventoryItem, User};
use Livewire\Volt\Volt;

use function Pest\Laravel\{actingAs, assertDatabaseHas, get};

beforeEach(function () {
    // Create admin user who has permission to create inventory
    $this->admin = User::factory()->admin()->create();
    actingAs($this->admin);
});

test('inventory create page can be rendered', function () {
    get(route('inventory.create'))
        ->assertOk()
        ->assertSeeLivewire(Create::class);
});

test('can create inventory item with all fields', function () {
    Volt::test(Create::class)
        ->set('name', 'iPhone 13 Screen')
        ->set('sku', 'IP13-SCR-001')
        ->set('description', 'Original Apple replacement screen')
        ->set('category', 'Parts')
        ->set('cost_price', '50.00')
        ->set('selling_price', '150.00')
        ->set('quantity', '25')
        ->set('reorder_level', '5')
        ->set('status', 'active')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('inventory.index'));

    assertDatabaseHas('inventory_items', [
        'name' => 'iPhone 13 Screen',
        'sku' => 'IP13-SCR-001',
        'description' => 'Original Apple replacement screen',
        'category' => 'Parts',
        'cost_price' => '50.00',
        'selling_price' => '150.00',
        'quantity' => 25,
        'reorder_level' => 5,
        'status' => 'active',
    ]);
});

test('can create inventory item with minimum required fields', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasNoErrors();

    assertDatabaseHas('inventory_items', [
        'name' => 'Test Item',
        'sku' => 'TEST-001',
    ]);
});

test('name is required', function () {
    Volt::test(Create::class)
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('sku is required', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasErrors(['sku' => 'required']);
});

test('sku must be unique', function () {
    InventoryItem::factory()->create(['sku' => 'DUPLICATE-SKU']);

    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'DUPLICATE-SKU')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasErrors(['sku' => 'unique']);
});

test('cost price is required', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasErrors(['cost_price' => 'required']);
});

test('cost price must be numeric', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', 'not-a-number')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasErrors(['cost_price' => 'numeric']);
});

test('cost price must be at least zero', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '-10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasErrors(['cost_price' => 'min']);
});

test('selling price is required', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasErrors(['selling_price' => 'required']);
});

test('selling price must be numeric', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', 'not-a-number')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasErrors(['selling_price' => 'numeric']);
});

test('selling price must be at least zero', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '-20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasErrors(['selling_price' => 'min']);
});

test('quantity is required', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasErrors(['quantity' => 'required']);
});

test('quantity must be an integer', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10.5')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasErrors(['quantity' => 'integer']);
});

test('quantity must be at least zero', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '-5')
        ->set('reorder_level', '2')
        ->call('save')
        ->assertHasErrors(['quantity' => 'min']);
});

test('reorder level is required', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->call('save')
        ->assertHasErrors(['reorder_level' => 'required']);
});

test('reorder level must be an integer', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2.5')
        ->call('save')
        ->assertHasErrors(['reorder_level' => 'integer']);
});

test('reorder level must be at least zero', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '-2')
        ->call('save')
        ->assertHasErrors(['reorder_level' => 'min']);
});

test('status must be valid', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->set('status', 'invalid-status')
        ->call('save')
        ->assertHasErrors(['status' => 'in']);
});

test('description is optional', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->set('description', null)
        ->call('save')
        ->assertHasNoErrors();
});

test('category is optional', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->set('category', null)
        ->call('save')
        ->assertHasNoErrors();
});

test('displays existing categories for suggestions', function () {
    InventoryItem::factory()->create(['category' => 'Parts']);
    InventoryItem::factory()->create(['category' => 'Tools']);

    Volt::test(Create::class)
        ->assertSee('Parts')
        ->assertSee('Tools');
});

test('unauthorized user cannot access create page', function () {
    // Create a technician user (not admin/manager)
    $technician = User::factory()->create(['role' => 'technician']);
    actingAs($technician);

    get(route('inventory.create'))
        ->assertForbidden();
});

test('flash message shown after successful creation', function () {
    Volt::test(Create::class)
        ->set('name', 'Test Item')
        ->set('sku', 'TEST-001')
        ->set('cost_price', '10.00')
        ->set('selling_price', '20.00')
        ->set('quantity', '10')
        ->set('reorder_level', '2')
        ->call('save');

    expect(session('success'))->toBe('Inventory item created successfully.');
});
