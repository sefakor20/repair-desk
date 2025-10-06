<?php

declare(strict_types=1);

use App\Enums\InventoryStatus;
use App\Livewire\Inventory\Edit;
use App\Models\{InventoryItem, User};

beforeEach(function (): void {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->user);

    $this->item = InventoryItem::factory()->create([
        'name' => 'Original Item',
        'sku' => 'ORIG-001',
        'description' => 'Original description',
        'category' => 'Original Category',
        'cost_price' => 100.00,
        'selling_price' => 150.00,
        'quantity' => 50,
        'reorder_level' => 10,
        'status' => InventoryStatus::Active,
    ]);
});

test('inventory edit page can be rendered', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->assertStatus(200)
        ->assertSee('Edit Inventory Item')
        ->assertSee('Update Item');
});

test('can update inventory item with all fields', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('name', 'Updated Item')
        ->set('sku', 'UPD-001')
        ->set('description', 'Updated description')
        ->set('category', 'Updated Category')
        ->set('cost_price', '200.00')
        ->set('selling_price', '250.00')
        ->set('quantity', '75')
        ->set('reorder_level', '15')
        ->set('status', 'inactive')
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('inventory.index'));

    $this->item->refresh();

    expect($this->item->name)->toBe('Updated Item')
        ->and($this->item->sku)->toBe('UPD-001')
        ->and($this->item->description)->toBe('Updated description')
        ->and($this->item->category)->toBe('Updated Category')
        ->and((float) $this->item->cost_price)->toBe(200.00)
        ->and((float) $this->item->selling_price)->toBe(250.00)
        ->and($this->item->quantity)->toBe(75)
        ->and($this->item->reorder_level)->toBe(15)
        ->and($this->item->status)->toBe(InventoryStatus::Inactive);
});

test('can update inventory item with minimum required fields', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('name', 'Updated Item')
        ->set('sku', 'UPD-001')
        ->set('description', null)
        ->set('category', null)
        ->set('cost_price', '200.00')
        ->set('selling_price', '250.00')
        ->set('quantity', '75')
        ->set('reorder_level', '15')
        ->set('status', 'active')
        ->call('update')
        ->assertHasNoErrors();

    $this->item->refresh();

    expect($this->item->name)->toBe('Updated Item')
        ->and($this->item->description)->toBeNull()
        ->and($this->item->category)->toBeNull();
});

test('name is required', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('name', '')
        ->call('update')
        ->assertHasErrors(['name' => 'required']);
});

test('sku is required', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('sku', '')
        ->call('update')
        ->assertHasErrors(['sku' => 'required']);
});

test('sku must be unique except for current item', function (): void {
    $otherItem = InventoryItem::factory()->create(['sku' => 'OTHER-001']);

    // Can keep the same SKU
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('sku', 'ORIG-001')
        ->call('update')
        ->assertHasNoErrors(['sku']);

    // Cannot use another item's SKU
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('sku', 'OTHER-001')
        ->call('update')
        ->assertHasErrors(['sku' => 'unique']);
});

test('cost price is required', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('cost_price', '')
        ->call('update')
        ->assertHasErrors(['cost_price' => 'required']);
});

test('cost price must be numeric', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('cost_price', 'not-a-number')
        ->call('update')
        ->assertHasErrors(['cost_price' => 'numeric']);
});

test('cost price must be at least zero', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('cost_price', '-10')
        ->call('update')
        ->assertHasErrors(['cost_price' => 'min']);
});

test('selling price is required', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('selling_price', '')
        ->call('update')
        ->assertHasErrors(['selling_price' => 'required']);
});

test('selling price must be numeric', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('selling_price', 'not-a-number')
        ->call('update')
        ->assertHasErrors(['selling_price' => 'numeric']);
});

test('selling price must be at least zero', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('selling_price', '-10')
        ->call('update')
        ->assertHasErrors(['selling_price' => 'min']);
});

test('quantity is required', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('quantity', '')
        ->call('update')
        ->assertHasErrors(['quantity' => 'required']);
});

test('quantity must be an integer', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('quantity', '10.5')
        ->call('update')
        ->assertHasErrors(['quantity' => 'integer']);
});

test('quantity must be at least zero', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('quantity', '-5')
        ->call('update')
        ->assertHasErrors(['quantity' => 'min']);
});

test('reorder level is required', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('reorder_level', '')
        ->call('update')
        ->assertHasErrors(['reorder_level' => 'required']);
});

test('reorder level must be an integer', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('reorder_level', '5.5')
        ->call('update')
        ->assertHasErrors(['reorder_level' => 'integer']);
});

test('reorder level must be at least zero', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('reorder_level', '-2')
        ->call('update')
        ->assertHasErrors(['reorder_level' => 'min']);
});

test('status must be valid', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('status', 'invalid')
        ->call('update')
        ->assertHasErrors(['status' => 'in']);
});

test('description is optional', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('description', null)
        ->call('update')
        ->assertHasNoErrors(['description']);
});

test('category is optional', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('category', null)
        ->call('update')
        ->assertHasNoErrors(['category']);
});

test('displays existing categories for suggestions', function (): void {
    InventoryItem::factory()->create(['category' => 'Electronics']);
    InventoryItem::factory()->create(['category' => 'Tools']);

    Livewire::test(Edit::class, ['item' => $this->item])
        ->assertSee('Electronics')
        ->assertSee('Tools');
});

test('form is pre-populated with item data', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->assertSet('name', 'Original Item')
        ->assertSet('sku', 'ORIG-001')
        ->assertSet('description', 'Original description')
        ->assertSet('category', 'Original Category')
        ->assertSet('cost_price', '100.00')
        ->assertSet('selling_price', '150.00')
        ->assertSet('quantity', '50')
        ->assertSet('reorder_level', '10')
        ->assertSet('status', 'active');
});

test('unauthorized user cannot access edit page', function (): void {
    $technician = User::factory()->create(['role' => 'technician']);

    $this->actingAs($technician);

    Livewire::test(Edit::class, ['item' => $this->item])
        ->assertForbidden();
});

test('flash message shown after successful update', function (): void {
    Livewire::test(Edit::class, ['item' => $this->item])
        ->set('name', 'Updated Item')
        ->call('update')
        ->assertSessionHas('success', 'Inventory item updated successfully.');
});
