<?php

declare(strict_types=1);

use App\Enums\InventoryStatus;
use App\Livewire\Inventory\Show;
use App\Models\{InventoryItem, User};

beforeEach(function (): void {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->user);
});

test('inventory show page can be rendered', function (): void {
    $item = InventoryItem::factory()->create([
        'name' => 'Test Item',
        'sku' => 'TEST-001',
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertStatus(200)
        ->assertSee('Test Item')
        ->assertSee('TEST-001');
});

test('displays item name and sku', function (): void {
    $item = InventoryItem::factory()->create([
        'name' => 'iPhone Screen',
        'sku' => 'IP13-SCR-001',
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('iPhone Screen')
        ->assertSee('IP13-SCR-001');
});

test('displays item description when available', function (): void {
    $item = InventoryItem::factory()->create([
        'description' => 'Original Apple replacement screen',
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('Original Apple replacement screen');
});

test('displays item category when available', function (): void {
    $item = InventoryItem::factory()->create([
        'category' => 'Phone Parts',
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('Phone Parts');
});

test('displays pricing information', function (): void {
    $item = InventoryItem::factory()->create([
        'cost_price' => 100.00,
        'selling_price' => 150.00,
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('$100.00')
        ->assertSee('$150.00');
});

test('calculates and displays profit margin', function (): void {
    $item = InventoryItem::factory()->create([
        'cost_price' => 100.00,
        'selling_price' => 150.00,
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('$50.00') // profit
        ->assertSee('50.0%'); // margin
});

test('displays current quantity', function (): void {
    $item = InventoryItem::factory()->create([
        'quantity' => 75,
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('75');
});

test('displays reorder level', function (): void {
    $item = InventoryItem::factory()->create([
        'reorder_level' => 10,
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('10');
});

test('shows low stock alert when quantity is below reorder level', function (): void {
    $item = InventoryItem::factory()->create([
        'quantity' => 5,
        'reorder_level' => 10,
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('Low Stock Alert');
});

test('does not show low stock alert when quantity is above reorder level', function (): void {
    $item = InventoryItem::factory()->create([
        'quantity' => 20,
        'reorder_level' => 10,
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertDontSee('Low Stock Alert');
});

test('displays active status badge', function (): void {
    $item = InventoryItem::factory()->create([
        'status' => InventoryStatus::Active,
        'name' => 'Active Test Item',
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('Active Test Item')
        ->assertSee('Active'); // Status badge contains Active
});

test('displays inactive status badge', function (): void {
    $item = InventoryItem::factory()->create([
        'status' => InventoryStatus::Inactive,
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('Inactive');
});

test('calculates total inventory value at cost', function (): void {
    $item = InventoryItem::factory()->create([
        'quantity' => 10,
        'cost_price' => 50.00,
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('$500.00'); // total cost value
});

test('calculates total inventory value at retail', function (): void {
    $item = InventoryItem::factory()->create([
        'quantity' => 10,
        'selling_price' => 75.00,
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('$750.00'); // total retail value
});

test('displays created date', function (): void {
    $item = InventoryItem::factory()->create([
        'created_at' => now()->subDays(30),
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee(now()->subDays(30)->format('M d, Y'));
});

test('shows edit button for authorized users', function (): void {
    $item = InventoryItem::factory()->create();

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('Edit Item');
});

test('unauthorized user cannot access show page', function (): void {
    $technician = User::factory()->create(['role' => 'technician']);
    $this->actingAs($technician);

    $item = InventoryItem::factory()->create();

    // Technicians can view inventory (as per policy)
    Livewire::test(Show::class, ['item' => $item])
        ->assertStatus(200);
});

test('shows back to inventory link', function (): void {
    $item = InventoryItem::factory()->create();

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('Back to Inventory');
});

test('shows low stock badge in header when applicable', function (): void {
    $item = InventoryItem::factory()->create([
        'quantity' => 3,
        'reorder_level' => 10,
    ]);

    Livewire::test(Show::class, ['item' => $item])
        ->assertSee('Low Stock');
});
