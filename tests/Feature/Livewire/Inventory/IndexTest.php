<?php

declare(strict_types=1);

use App\Enums\InventoryStatus;
use App\Livewire\Inventory\Index;
use App\Models\{InventoryItem, User};
use Livewire\Volt\Volt;

use function Pest\Laravel\{actingAs, get};

beforeEach(function (): void {
    $this->user = createAdmin();
    actingAs($this->user);
});

test('inventory page can be rendered', function (): void {
    get(route('inventory.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

test('inventory page displays inventory items', function (): void {
    $item = InventoryItem::factory()->create([
        'name' => 'iPhone Screen',
        'sku' => 'IP-SCR-001',
        'category' => 'Parts',
        'quantity' => 50,
        'cost_price' => 25.00,
        'selling_price' => 75.00,
    ]);

    Volt::test(Index::class)
        ->assertSee('iPhone Screen')
        ->assertSee('IP-SCR-001')
        ->assertSee('Parts')
        ->assertSee('50')
        ->assertSee('GHS', false)
        ->assertSee('25.00')
        ->assertSee('GHS', false)
        ->assertSee('75.00');
});

test('inventory page shows empty state when no items exist', function (): void {
    Volt::test(Index::class)
        ->assertSee('No inventory items yet.');
});

test('inventory page shows filtered empty state', function (): void {
    InventoryItem::factory()->create(['name' => 'iPhone Screen']);

    Volt::test(Index::class)
        ->set('search', 'Samsung')
        ->assertSee('No inventory items found matching your filters.');
});

test('can search inventory by name', function (): void {
    InventoryItem::factory()->create(['name' => 'iPhone Screen']);
    InventoryItem::factory()->create(['name' => 'Samsung Battery']);

    Volt::test(Index::class)
        ->set('search', 'iPhone')
        ->assertSee('iPhone Screen')
        ->assertDontSee('Samsung Battery');
});

test('can search inventory by sku', function (): void {
    InventoryItem::factory()->create(['name' => 'iPhone Screen', 'sku' => 'IP-001']);
    InventoryItem::factory()->create(['name' => 'Samsung Battery', 'sku' => 'SM-002']);

    Volt::test(Index::class)
        ->set('search', 'IP-001')
        ->assertSee('iPhone Screen')
        ->assertDontSee('Samsung Battery');
});

test('can search inventory by description', function (): void {
    InventoryItem::factory()->create([
        'name' => 'Item 1',
        'description' => 'Original Apple replacement screen',
    ]);
    InventoryItem::factory()->create([
        'name' => 'Item 2',
        'description' => 'Generic battery pack',
    ]);

    Volt::test(Index::class)
        ->set('search', 'Apple')
        ->assertSee('Item 1')
        ->assertDontSee('Item 2');
});

test('can filter inventory by status', function (): void {
    InventoryItem::factory()->create([
        'name' => 'Active Item',
        'status' => InventoryStatus::Active,
    ]);
    InventoryItem::factory()->create([
        'name' => 'Inactive Item',
        'status' => InventoryStatus::Inactive,
    ]);

    Volt::test(Index::class)
        ->set('status', 'active')
        ->assertSee('Active Item')
        ->assertDontSee('Inactive Item');
});

test('can filter inventory by category', function (): void {
    InventoryItem::factory()->create(['name' => 'Screen', 'category' => 'Parts']);
    InventoryItem::factory()->create(['name' => 'Toolkit', 'category' => 'Tools']);

    Volt::test(Index::class)
        ->set('category', 'Parts')
        ->assertSee('Screen')
        ->assertDontSee('Toolkit');
});

test('can filter for low stock items only', function (): void {
    InventoryItem::factory()->create([
        'name' => 'Low Stock Item',
        'quantity' => 5,
        'reorder_level' => 10,
    ]);
    InventoryItem::factory()->create([
        'name' => 'Sufficient Stock Item',
        'quantity' => 100,
        'reorder_level' => 10,
    ]);

    Volt::test(Index::class)
        ->set('lowStock', true)
        ->assertSee('Low Stock Item')
        ->assertDontSee('Sufficient Stock Item');
});

test('highlights low stock items with badge', function (): void {
    InventoryItem::factory()->create([
        'name' => 'Low Stock Item',
        'quantity' => 3,
        'reorder_level' => 10,
    ]);

    Volt::test(Index::class)
        ->assertSee('Low');
});

test('displays reorder level for each item', function (): void {
    InventoryItem::factory()->create([
        'name' => 'Test Item',
        'reorder_level' => 15,
    ]);

    Volt::test(Index::class)
        ->assertSee('Reorder at: 15');
});

test('can clear all filters', function (): void {
    InventoryItem::factory()->create(['name' => 'Test Item']);

    Volt::test(Index::class)
        ->set('search', 'something')
        ->set('status', 'active')
        ->set('category', 'Parts')
        ->set('lowStock', true)
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('status', '')
        ->assertSet('category', '')
        ->assertSet('lowStock', false);
});

test('search resets pagination', function (): void {
    InventoryItem::factory()->count(20)->create();

    $component = Volt::test(Index::class);

    // Navigate to page 2
    $component->set('paginators.page', 2);

    // Search should reset to page 1
    $component->set('search', 'test');

    expect($component->get('paginators.page'))->toBe(1);
});

test('can delete inventory item', function (): void {
    // Create an admin user who has permission to delete inventory
    $admin = User::factory()->admin()->create();
    actingAs($admin);

    $item = InventoryItem::factory()->create(['name' => 'To Delete']);

    Volt::test(Index::class)
        ->call('confirmDelete', $item->id)
        ->assertSet('deletingItemId', $item->id)
        ->call('delete');

    expect(InventoryItem::where('name', 'To Delete')->exists())->toBeFalse();
});

test('displays active status badge correctly', function (): void {
    InventoryItem::factory()->create([
        'name' => 'Active Item',
        'status' => InventoryStatus::Active,
    ]);

    Volt::test(Index::class)
        ->assertSee('Active');
});

test('displays inactive status badge correctly', function (): void {
    InventoryItem::factory()->create([
        'name' => 'Inactive Item',
        'status' => InventoryStatus::Inactive,
    ]);

    Volt::test(Index::class)
        ->assertSee('Inactive');
});

test('inventory items are paginated', function (): void {
    InventoryItem::factory()->count(20)->create();

    $component = Volt::test(Index::class);

    // Should see pagination
    $component->assertSee('Next');
});

test('displays categories in filter dropdown', function (): void {
    InventoryItem::factory()->create(['category' => 'Parts']);
    InventoryItem::factory()->create(['category' => 'Tools']);

    Volt::test(Index::class)
        ->assertSee('Parts')
        ->assertSee('Tools');
});

test('shows clear filters button when filters are active', function (): void {
    Volt::test(Index::class)
        ->set('search', 'test')
        ->assertSee('Clear filters');
});

test('hides clear filters button when no filters are active', function (): void {
    Volt::test(Index::class)
        ->assertDontSee('Clear filters');
});

test('unauthorized user cannot access inventory page', function (): void {
    $this->app['auth']->forgetGuards();

    get(route('inventory.index'))
        ->assertRedirect(route('login'));
});

test('displays truncated description for long descriptions', function (): void {
    InventoryItem::factory()->create([
        'name' => 'Test Item',
        'description' => 'This is a very long description that should be truncated to ensure the table layout remains clean and readable for all users',
    ]);

    Volt::test(Index::class)
        ->assertSee('This is a very long description that sho...');
});
