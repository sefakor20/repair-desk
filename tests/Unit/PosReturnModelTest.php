<?php

declare(strict_types=1);

use App\Enums\{PosSaleStatus, ReturnReason, ReturnStatus};
use App\Models\{InventoryItem, PosReturn, PosSale, PosSaleItem, ReturnPolicy};

test('can calculate return totals correctly', function (): void {
    // Create a return policy with restocking fee: 10% with $21 minimum
    $policy = ReturnPolicy::factory()->create([
        'restocking_fee_percentage' => 10,
        'minimum_restocking_fee' => 21.00,
        'is_active' => true,
    ]);

    $sale = PosSale::factory()->create([
        'tax_rate' => 5.00,
        'return_policy_id' => $policy->id,
    ]);

    $return = PosReturn::factory()->create([
        'original_sale_id' => $sale->id,
        'return_reason' => ReturnReason::CustomerChanged, // Requires restocking fee
        'subtotal_returned' => 0,
        'tax_returned' => 0,
        'restocking_fee' => 0,
        'total_refund_amount' => 0,
    ]);

    $item = InventoryItem::factory()->create();
    $saleItem = PosSaleItem::factory()->create([
        'pos_sale_id' => $sale->id,
        'inventory_item_id' => $item->id,
        'unit_price' => 100.00,
        'quantity' => 2,
    ]);

    $return->items()->create([
        'original_sale_item_id' => $saleItem->id,
        'inventory_item_id' => $item->id,
        'quantity_returned' => 2,
        'unit_price' => 100.00,
        'subtotal' => 200.00,
        'line_refund_amount' => 200.00,
    ]);

    $return->calculateTotals(10.00);

    expect($return->subtotal_returned)->toEqual(200.00)
        ->and($return->tax_returned)->toEqual(10.00)
        ->and($return->restocking_fee)->toEqual(21.00)
        ->and($return->total_refund_amount)->toEqual(189.00);
});

test('can restore inventory correctly', function (): void {
    $item = InventoryItem::factory()->create(['quantity' => 10]);
    $sale = PosSale::factory()->create();
    $saleItem = PosSaleItem::factory()->create([
        'pos_sale_id' => $sale->id,
        'inventory_item_id' => $item->id,
        'quantity' => 2,
    ]);

    $return = PosReturn::factory()->create([
        'original_sale_id' => $sale->id,
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

    $return->restoreInventory();

    $item->refresh();
    expect($item->quantity)->toBe(12)
        ->and($return->inventory_restored)->toBeTrue();
});

test('prevents double restoration of inventory', function (): void {
    $item = InventoryItem::factory()->create(['quantity' => 10]);
    $sale = PosSale::factory()->create();
    $saleItem = PosSaleItem::factory()->create([
        'pos_sale_id' => $sale->id,
        'inventory_item_id' => $item->id,
        'quantity' => 2,
    ]);

    $return = PosReturn::factory()->create([
        'original_sale_id' => $sale->id,
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

    $return->restoreInventory();
    $return->restoreInventory(); // Try to restore again

    $item->refresh();
    expect($item->quantity)->toBe(12); // Should still be 12, not 14
});

test('checks if return can be processed', function (): void {
    $pendingReturn = PosReturn::factory()->create(['status' => ReturnStatus::Pending]);
    $approvedReturn = PosReturn::factory()->create(['status' => ReturnStatus::Approved]);
    $processingReturn = PosReturn::factory()->create(['status' => ReturnStatus::Processing]);
    $rejectedReturn = PosReturn::factory()->create(['status' => ReturnStatus::Rejected]);

    expect($pendingReturn->canBeProcessed())->toBeFalse()
        ->and($approvedReturn->canBeProcessed())->toBeTrue()
        ->and($processingReturn->canBeProcessed())->toBeTrue()
        ->and($rejectedReturn->canBeProcessed())->toBeFalse();
});

test('generates return number with correct format', function (): void {
    $return = PosReturn::factory()->create();
    $returnNumber = $return->generateReturnNumber();

    expect($returnNumber)
        ->toBeString()
        ->toStartWith('RET-')
        ->toContain(now()->format('Ymd'));
});

test('checks if sale is within return window', function (): void {
    $policy = \App\Models\ReturnPolicy::factory()->create([
        'return_window_days' => 30,
        'is_active' => true,
    ]);

    $recentSale = PosSale::factory()->create([
        'sale_date' => now()->subDays(5),
        'return_policy_id' => $policy->id,
    ]);

    $oldSale = PosSale::factory()->create([
        'sale_date' => now()->subDays(100),
        'return_policy_id' => $policy->id,
    ]);

    $recentReturn = PosReturn::factory()->create([
        'original_sale_id' => $recentSale->id,
        'return_date' => now(), // Return created now, 5 days after sale
    ]);
    $oldReturn = PosReturn::factory()->create([
        'original_sale_id' => $oldSale->id,
        'return_date' => now(), // Return created now, 100 days after sale
    ]);

    expect($recentReturn->isWithinReturnWindow())->toBeTrue()
        ->and($oldReturn->isWithinReturnWindow())->toBeFalse();
});

test('sale can be returned only when completed', function (): void {
    $completedSale = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sale_date' => now()->subDay(),
    ]);

    $refundedSale = PosSale::factory()->create([
        'status' => PosSaleStatus::Refunded,
        'sale_date' => now()->subDay(),
    ]);

    expect($completedSale->canBeReturned())->toBeTrue()
        ->and($refundedSale->canBeReturned())->toBeFalse();
});

test('sale cannot be returned if outside return window', function (): void {
    $policy = \App\Models\ReturnPolicy::factory()->create([
        'return_window_days' => 30,
        'is_active' => true,
    ]);

    $oldSale = PosSale::factory()->create([
        'status' => PosSaleStatus::Completed,
        'sale_date' => now()->subDays(100),
        'return_policy_id' => $policy->id,
    ]);

    // Return created now would be 100 days after the sale, outside the 30-day window
    expect($oldSale->canBeReturned())->toBeFalse();
});

test('return item calculates subtotal correctly', function (): void {
    $return = PosReturn::factory()->create();
    $item = $return->items()->create([
        'original_sale_item_id' => PosSaleItem::factory()->create()->id,
        'inventory_item_id' => InventoryItem::factory()->create()->id,
        'quantity_returned' => 3,
        'unit_price' => 50.00,
        'subtotal' => 0,
        'line_refund_amount' => 0,
    ]);

    $item->calculateSubtotal();

    expect($item->subtotal)->toEqual(150.00);
});
