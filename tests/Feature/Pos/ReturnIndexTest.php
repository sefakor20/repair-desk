<?php

declare(strict_types=1);

use App\Enums\{ReturnStatus};
use App\Livewire\Pos\ReturnIndex;
use App\Models\{Customer, InventoryItem, PosReturn, PosSale, PosSaleItem, User};
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can mount return index page', function () {
    Livewire::test(ReturnIndex::class)
        ->assertSuccessful()
        ->assertSee('Returns', false)
        ->assertSee('Refunds', false);
});

test('displays all returns by default', function () {
    $returns = PosReturn::factory()->count(3)->create();

    Livewire::test(ReturnIndex::class)
        ->assertSuccessful()
        ->assertSee($returns[0]->return_number)
        ->assertSee($returns[1]->return_number)
        ->assertSee($returns[2]->return_number);
});

test('can filter returns by status', function () {
    $pendingReturn = PosReturn::factory()->create(['status' => ReturnStatus::Pending]);
    $approvedReturn = PosReturn::factory()->create(['status' => ReturnStatus::Approved]);

    Livewire::test(ReturnIndex::class)
        ->set('status', 'pending')
        ->assertSee($pendingReturn->return_number)
        ->assertDontSee($approvedReturn->return_number);
});

test('can search returns by return number', function () {
    $return1 = PosReturn::factory()->create(['return_number' => 'RET-001']);
    $return2 = PosReturn::factory()->create(['return_number' => 'RET-002']);

    Livewire::test(ReturnIndex::class)
        ->set('search', 'RET-001')
        ->assertSee($return1->return_number)
        ->assertDontSee($return2->return_number);
});

test('can search returns by sale number', function () {
    $sale = PosSale::factory()->create(['sale_number' => 'SALE-12345']);
    $return = PosReturn::factory()->create(['original_sale_id' => $sale->id]);

    Livewire::test(ReturnIndex::class)
        ->set('search', 'SALE-12345')
        ->assertSee($return->return_number);
});

test('can search returns by customer name', function () {
    $customer = Customer::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
    $sale = PosSale::factory()->create(['customer_id' => $customer->id]);
    $return = PosReturn::factory()->create([
        'original_sale_id' => $sale->id,
        'customer_id' => $customer->id,
    ]);

    Livewire::test(ReturnIndex::class)
        ->set('search', 'John Doe')
        ->assertSee($return->return_number, false);
});

test('displays correct stats', function () {
    PosReturn::factory()->count(2)->create(['status' => ReturnStatus::Pending]);
    PosReturn::factory()->count(3)->create(['status' => ReturnStatus::Approved]);
    PosReturn::factory()->count(1)->create([
        'status' => ReturnStatus::Completed,
        'total_refund_amount' => 100.00,
    ]);

    $component = Livewire::test(ReturnIndex::class);

    // Access the stats directly through the component's computed property
    $stats = $component->get('stats');
    expect($stats['pending_count'])->toBe(2)
        ->and($stats['approved_count'])->toBe(3)
        ->and($stats['completed_count'])->toBe(1)
        ->and($stats['total_refunded'])->toEqual(100.00);
});

test('can approve pending return', function () {
    $return = PosReturn::factory()->create([
        'status' => ReturnStatus::Pending,
        'inventory_restored' => false,
    ]);

    Livewire::test(ReturnIndex::class)
        ->call('approveReturn', $return->id)
        ->assertSuccessful();

    $return->refresh();
    expect($return->status)->toBe(ReturnStatus::Approved)
        ->and($return->refunded_at)->not->toBeNull()
        ->and($return->inventory_restored)->toBeTrue();
});

test('can reject pending return', function () {
    $return = PosReturn::factory()->create(['status' => ReturnStatus::Pending]);

    Livewire::test(ReturnIndex::class)
        ->call('rejectReturn', $return->id)
        ->assertSuccessful();

    $return->refresh();
    expect($return->status)->toBe(ReturnStatus::Rejected);
});

test('displays empty state when no returns', function () {
    Livewire::test(ReturnIndex::class)
        ->assertSee('No returns')
        ->assertSee('found');
});

test('displays filtered empty state', function () {
    PosReturn::factory()->create(['status' => ReturnStatus::Pending]);

    Livewire::test(ReturnIndex::class)
        ->set('status', 'completed')
        ->assertSee('Try adjusting your filters');
});

test('paginates returns', function () {
    PosReturn::factory()->count(20)->create();

    Livewire::test(ReturnIndex::class)
        ->assertSee('1')
        ->assertSee('2');
});

test('url query params are synced with filters', function () {
    Livewire::test(ReturnIndex::class)
        ->set('search', 'test')
        ->set('status', 'pending')
        ->assertSet('search', 'test')
        ->assertSet('status', 'pending');
});

test('restores inventory when approving return', function () {
    $item = InventoryItem::factory()->create(['quantity' => 10]);
    $sale = PosSale::factory()->create();
    $saleItem = PosSaleItem::factory()->create([
        'pos_sale_id' => $sale->id,
        'inventory_item_id' => $item->id,
        'quantity' => 2,
    ]);

    $return = PosReturn::factory()->create([
        'original_sale_id' => $sale->id,
        'status' => ReturnStatus::Pending,
        'inventory_restored' => false,
    ]);

    $return->items()->create([
        'original_sale_item_id' => $saleItem->id,
        'inventory_item_id' => $item->id,
        'quantity_returned' => 2,
        'unit_price' => 100.00,
        'subtotal' => 200.00,
        'line_refund_amount' => 200.00,
    ]);

    Livewire::test(ReturnIndex::class)
        ->call('approveReturn', $return->id);

    $item->refresh();
    expect($item->quantity)->toBe(12);
});
