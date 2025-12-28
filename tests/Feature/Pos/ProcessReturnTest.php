<?php

declare(strict_types=1);

use App\Enums\{PosSaleStatus, ReturnReason, ReturnStatus};
use App\Livewire\Pos\ProcessReturn;
use App\Models\{Customer, InventoryItem, PosReturn, PosSale, PosSaleItem, User};
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->customer = Customer::factory()->create();
    $this->item = InventoryItem::factory()->create([
        'selling_price' => 100.00,
        'quantity' => 10,
    ]);

    $this->sale = PosSale::factory()->create([
        'customer_id' => $this->customer->id,
        'status' => PosSaleStatus::Completed,
        'subtotal' => 200.00,
        'tax_amount' => 10.00,
        'total_amount' => 210.00,
        'tax_rate' => 5.00,
    ]);

    $this->saleItem = PosSaleItem::factory()->create([
        'pos_sale_id' => $this->sale->id,
        'inventory_item_id' => $this->item->id,
        'quantity' => 2,
        'unit_price' => 100.00,
        'subtotal' => 200.00,
    ]);
});

test('can mount process return page', function (): void {
    Livewire::test(ProcessReturn::class, ['sale' => $this->sale])
        ->assertSuccessful()
        ->assertSee($this->sale->sale_number);
});

test('can process return with all items', function (): void {
    Livewire::test(ProcessReturn::class, ['sale' => $this->sale])
        ->set('returnReason', ReturnReason::Defective->value)
        ->set('selectedItems.' . $this->saleItem->id . '.selected', true)
        ->set('selectedItems.' . $this->saleItem->id . '.quantity', 2)
        ->set('autoApprove', true)
        ->set('restoreInventory', true)
        ->call('processReturn')
        ->assertHasNoErrors()
        ->assertRedirect(route('pos.returns.index'));

    $return = PosReturn::latest()->first();

    expect($return)->not->toBeNull()
        ->and($return->original_sale_id)->toBe($this->sale->id)
        ->and($return->return_reason)->toBe(ReturnReason::Defective)
        ->and($return->status)->toBe(ReturnStatus::Approved)
        ->and($return->items()->count())->toBe(1)
        ->and($return->inventory_restored)->toBeTrue();
});

test('can process partial return', function (): void {
    Livewire::test(ProcessReturn::class, ['sale' => $this->sale])
        ->set('returnReason', ReturnReason::CustomerChanged->value)
        ->set('selectedItems.' . $this->saleItem->id . '.selected', true)
        ->set('selectedItems.' . $this->saleItem->id . '.quantity', 1)
        ->set('autoApprove', false)
        ->call('processReturn')
        ->assertHasNoErrors();

    $return = PosReturn::latest()->first();

    expect($return->status)->toBe(ReturnStatus::Pending)
        ->and($return->items()->first()->quantity_returned)->toBe(1);
});

test('calculates refund correctly with restocking fee', function (): void {
    Livewire::test(ProcessReturn::class, ['sale' => $this->sale])
        ->set('returnReason', ReturnReason::CustomerChanged->value)
        ->set('restockingFeePercentage', 10.00)
        ->set('selectedItems.' . $this->saleItem->id . '.selected', true)
        ->set('selectedItems.' . $this->saleItem->id . '.quantity', 2)
        ->assertSet('subtotalReturned', 200.00)
        ->assertSet('taxReturned', 10.00)
        ->assertSet('restockingFee', 21.00) // 10% of (200 + 10)
        ->assertSet('totalRefund', 189.00); // (200 + 10) - 21
});

test('restocking fee adjusts when return reason changes', function (): void {
    Livewire::test(ProcessReturn::class, ['sale' => $this->sale])
        ->set('returnReason', ReturnReason::CustomerChanged->value)
        ->set('selectedItems.' . $this->saleItem->id . '.selected', true)
        ->assertSet('restockingFeePercentage', 10.00)
        ->set('returnReason', ReturnReason::Defective->value)
        ->assertSet('restockingFeePercentage', 0.00);
});

test('validates return reason is required', function (): void {
    Livewire::test(ProcessReturn::class, ['sale' => $this->sale])
        ->set('selectedItems.' . $this->saleItem->id . '.selected', true)
        ->call('processReturn')
        ->assertHasErrors(['returnReason' => 'required']);
});

test('validates at least one item must be selected', function (): void {
    Livewire::test(ProcessReturn::class, ['sale' => $this->sale])
        ->set('returnReason', ReturnReason::Defective->value)
        ->call('processReturn')
        ->assertHasErrors(['selectedItems']);
});

test('validates return notes max length', function (): void {
    Livewire::test(ProcessReturn::class, ['sale' => $this->sale])
        ->set('returnNotes', str_repeat('a', 501))
        ->set('returnReason', ReturnReason::Defective->value)
        ->set('selectedItems.' . $this->saleItem->id . '.selected', true)
        ->call('processReturn')
        ->assertHasErrors(['returnNotes' => 'max']);
});

test('restores inventory when auto-approved and restore flag is true', function (): void {
    $initialQuantity = $this->item->quantity;

    Livewire::test(ProcessReturn::class, ['sale' => $this->sale])
        ->set('returnReason', ReturnReason::Defective->value)
        ->set('selectedItems.' . $this->saleItem->id . '.selected', true)
        ->set('selectedItems.' . $this->saleItem->id . '.quantity', 2)
        ->set('autoApprove', true)
        ->set('restoreInventory', true)
        ->call('processReturn')
        ->assertHasNoErrors();

    $this->item->refresh();
    expect($this->item->quantity)->toBe($initialQuantity + 2);
});

test('does not restore inventory when restore flag is false', function (): void {
    $initialQuantity = $this->item->quantity;

    Livewire::test(ProcessReturn::class, ['sale' => $this->sale])
        ->set('returnReason', ReturnReason::Defective->value)
        ->set('selectedItems.' . $this->saleItem->id . '.selected', true)
        ->set('selectedItems.' . $this->saleItem->id . '.quantity', 2)
        ->set('autoApprove', true)
        ->set('restoreInventory', false)
        ->call('processReturn')
        ->assertHasNoErrors();

    $this->item->refresh();
    expect($this->item->quantity)->toBe($initialQuantity);
});

test('tracks item condition for each returned item', function (): void {
    Livewire::test(ProcessReturn::class, ['sale' => $this->sale])
        ->set('returnReason', ReturnReason::Damaged->value)
        ->set('selectedItems.' . $this->saleItem->id . '.selected', true)
        ->set('selectedItems.' . $this->saleItem->id . '.quantity', 2)
        ->set('selectedItems.' . $this->saleItem->id . '.condition', 'damaged')
        ->set('selectedItems.' . $this->saleItem->id . '.notes', 'Water damage on screen')
        ->set('autoApprove', true)
        ->call('processReturn')
        ->assertHasNoErrors();

    $returnItem = PosReturn::latest()->first()->items()->first();

    expect($returnItem->item_condition)->toBe('damaged')
        ->and($returnItem->item_notes)->toBe('Water damage on screen');
});

test('generates unique return number', function (): void {
    Livewire::test(ProcessReturn::class, ['sale' => $this->sale])
        ->set('returnReason', ReturnReason::Defective->value)
        ->set('selectedItems.' . $this->saleItem->id . '.selected', true)
        ->set('autoApprove', true)
        ->call('processReturn');

    $return = PosReturn::latest()->first();

    expect($return->return_number)
        ->toBeString()
        ->toStartWith('RET-');
});
