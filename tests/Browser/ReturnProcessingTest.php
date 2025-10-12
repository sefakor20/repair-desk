<?php

declare(strict_types=1);

use App\Enums\{PosSaleStatus, ReturnStatus};
use App\Models\{Customer, InventoryItem, PosSale, PosSaleItem, User};

uses()->group('browser');

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->customer = Customer::factory()->create();

    // Create inventory item
    $this->item = InventoryItem::factory()->create([
        'name' => 'Defective Phone Case',
        'sku' => 'TEST-CASE-001',
        'price' => 30.00,
        'quantity' => 10,
    ]);

    // Create a completed sale
    $this->sale = PosSale::factory()->create([
        'customer_id' => $this->customer->id,
        'user_id' => $this->user->id,
        'status' => PosSaleStatus::Completed,
        'subtotal_amount' => 30.00,
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

it('processes a complete return successfully', function () {
    $page = visit('/returns')->actingAs($this->user);

    // Navigate to returns page
    $page->assertSee('Returns & Refunds')
        ->assertNoJavaScriptErrors();

    // Search for sale by invoice number
    $page->click('Process Return')
        ->type('@sale-search', $this->sale->invoice_number)
        ->waitFor('@sale-' . $this->sale->id)
        ->click('@sale-' . $this->sale->id)
        ->assertSee($this->customer->name)
        ->assertSee('Defective Phone Case');

    // Select item for return
    $page->check('@return-item-' . $this->item->id)
        ->type('@return-reason', 'Product defective')
        ->click('@submit-return')
        ->assertSee('Return created successfully')
        ->assertNoJavaScriptErrors();

    // Verify return was created
    expect(\App\Models\PosReturn::count())->toBe(1);

    $return = \App\Models\PosReturn::first();
    expect($return->status)->toBe(ReturnStatus::Pending)
        ->and($return->reason)->toBe('Product defective')
        ->and($return->total_amount)->toBe(34.50);
});

it('approves a pending return and restores inventory', function () {
    // Create a pending return
    $return = \App\Models\PosReturn::factory()->create([
        'pos_sale_id' => $this->sale->id,
        'status' => ReturnStatus::Pending,
        'total_amount' => 34.50,
        'reason' => 'Defective product',
    ]);

    \App\Models\PosReturnItem::create([
        'pos_return_id' => $return->id,
        'inventory_item_id' => $this->item->id,
        'quantity' => 1,
        'refund_amount' => 30.00,
    ]);

    $originalQuantity = $this->item->quantity;

    $page = visit('/returns')->actingAs($this->user);

    // Find and approve return
    $page->click('@return-' . $return->id)
        ->assertSee('Defective product')
        ->assertSee($this->customer->name)
        ->click('@approve-return')
        ->type('@approval-notes', 'Approved - defective item confirmed')
        ->click('@confirm-approval')
        ->assertSee('Return approved successfully')
        ->assertNoJavaScriptErrors();

    // Verify return status updated
    $return->refresh();
    expect($return->status)->toBe(ReturnStatus::Approved);

    // Verify inventory restored
    $this->item->refresh();
    expect($this->item->quantity)->toBe($originalQuantity + 1);
});

it('rejects a return with reason', function () {
    // Create a pending return
    $return = \App\Models\PosReturn::factory()->create([
        'pos_sale_id' => $this->sale->id,
        'status' => ReturnStatus::Pending,
        'total_amount' => 34.50,
        'reason' => 'Changed mind',
    ]);

    $originalQuantity = $this->item->quantity;

    $page = visit('/returns')->actingAs($this->user);

    // Find and reject return
    $page->click('@return-' . $return->id)
        ->click('@reject-return')
        ->type('@rejection-reason', 'Does not meet return policy - outside return window')
        ->click('@confirm-rejection')
        ->assertSee('Return rejected')
        ->assertNoJavaScriptErrors();

    // Verify return status updated
    $return->refresh();
    expect($return->status)->toBe(ReturnStatus::Rejected);

    // Verify inventory NOT restored
    $this->item->refresh();
    expect($this->item->quantity)->toBe($originalQuantity);
});

it('filters returns by status', function () {
    // Create multiple returns with different statuses
    $pendingReturn = \App\Models\PosReturn::factory()->create([
        'pos_sale_id' => $this->sale->id,
        'status' => ReturnStatus::Pending,
    ]);

    $approvedReturn = \App\Models\PosReturn::factory()->create([
        'pos_sale_id' => $this->sale->id,
        'status' => ReturnStatus::Approved,
    ]);

    $page = visit('/returns')->actingAs($this->user);

    // Filter by pending
    $page->select('@status-filter', 'pending')
        ->waitFor('@return-' . $pendingReturn->id)
        ->assertSee('Pending')
        ->assertDontSee('Approved');

    // Filter by approved
    $page->select('@status-filter', 'approved')
        ->waitFor('@return-' . $approvedReturn->id)
        ->assertSee('Approved')
        ->assertDontSee('Pending');

    // Show all
    $page->select('@status-filter', 'all')
        ->assertSee('Pending')
        ->assertSee('Approved');
});

it('validates return reason is required', function () {
    $page = visit('/returns')->actingAs($this->user);

    // Try to submit return without reason
    $page->click('Process Return')
        ->type('@sale-search', $this->sale->invoice_number)
        ->waitFor('@sale-' . $this->sale->id)
        ->click('@sale-' . $this->sale->id)
        ->check('@return-item-' . $this->item->id)
        ->click('@submit-return')
        ->assertSee('The reason field is required')
        ->assertNoJavaScriptErrors();

    // Verify return was NOT created
    expect(\App\Models\PosReturn::count())->toBe(0);
});

it('displays return history for a customer', function () {
    // Create multiple returns
    \App\Models\PosReturn::factory()->count(3)->create([
        'pos_sale_id' => $this->sale->id,
    ]);

    $page = visit("/customers/{$this->customer->id}")->actingAs($this->user);

    // Navigate to returns tab
    $page->click('@customer-returns-tab')
        ->assertSee('Return History')
        ->assertCount('.return-item', 3)
        ->assertNoJavaScriptErrors();
});
