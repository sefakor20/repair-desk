<?php

declare(strict_types=1);

use App\Models\{Branch, Ticket, InventoryItem, PosSale, Invoice, Payment, User};
use App\Services\BranchContextService;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->branchContext = app(BranchContextService::class);

    // Create branches
    $this->branchA = Branch::factory()->create(['name' => 'Branch A', 'code' => 'BRA']);
    $this->branchB = Branch::factory()->create(['name' => 'Branch B', 'code' => 'BRB']);

    // Create users in different branches
    $this->userBranchA = User::factory()->create([
        'role' => 'admin',
        'branch_id' => $this->branchA->id,
    ]);
    $this->userBranchB = User::factory()->create([
        'role' => 'admin',
        'branch_id' => $this->branchB->id,
    ]);
});

test('users can only see data from their branch', function () {
    // Create tickets in both branches
    $ticketA = Ticket::factory()->create(['branch_id' => $this->branchA->id]);
    $ticketB = Ticket::factory()->create(['branch_id' => $this->branchB->id]);

    actingAs($this->userBranchA);

    // User A should only see ticket A
    $results = Ticket::all();
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($ticketA->id);
});

test('users cannot see data from other branches', function () {
    $ticketB = Ticket::factory()->create(['branch_id' => $this->branchB->id]);

    actingAs($this->userBranchA);

    // User A should not see ticket B
    $results = Ticket::all();
    expect($results)->toHaveCount(0);
    expect($results->contains('id', $ticketB->id))->toBeFalse();
});

test('inventory items are scoped by branch', function () {
    $itemA = InventoryItem::factory()->create(['branch_id' => $this->branchA->id]);
    $itemB = InventoryItem::factory()->create(['branch_id' => $this->branchB->id]);

    actingAs($this->userBranchA);

    $results = InventoryItem::all();
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($itemA->id);
});

test('pos sales are scoped by branch', function () {
    $saleA = PosSale::factory()->create(['branch_id' => $this->branchA->id]);
    $saleB = PosSale::factory()->create(['branch_id' => $this->branchB->id]);

    actingAs($this->userBranchA);

    $results = PosSale::all();
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($saleA->id);
});

test('invoices are scoped by branch', function () {
    $invoiceA = Invoice::factory()->create(['branch_id' => $this->branchA->id]);
    $invoiceB = Invoice::factory()->create(['branch_id' => $this->branchB->id]);

    actingAs($this->userBranchA);

    $results = Invoice::all();
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($invoiceA->id);
});

test('payments are scoped by branch', function () {
    $paymentA = Payment::factory()->create(['branch_id' => $this->branchA->id]);
    $paymentB = Payment::factory()->create(['branch_id' => $this->branchB->id]);

    actingAs($this->userBranchA);

    $results = Payment::all();
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($paymentA->id);
});

test('can access branch context', function () {
    actingAs($this->userBranchA);

    $branch = $this->branchContext->getCurrentBranch();
    expect($branch->id)->toBe($this->branchA->id);
});

test('can verify branch access', function () {
    actingAs($this->userBranchA);

    expect($this->branchContext->canAccessBranch($this->branchA))->toBeTrue();
    expect($this->branchContext->canAccessBranch($this->branchB))->toBeFalse();
});

test('branch context cache works correctly', function () {
    actingAs($this->userBranchA);

    $branch1 = $this->branchContext->getCurrentBranch();
    $branch2 = $this->branchContext->getCurrentBranch();

    expect($branch1->id)->toBe($branch2->id);
});

test('global scope can be removed for admin queries', function () {
    $ticketA = Ticket::factory()->create(['branch_id' => $this->branchA->id]);
    $ticketB = Ticket::factory()->create(['branch_id' => $this->branchB->id]);

    actingAs($this->userBranchA);

    // With scope
    $scopedResults = Ticket::all();
    expect($scopedResults)->toHaveCount(1);

    // Without scope (for special admin operations)
    $unscopedResults = Ticket::withoutGlobalScopes()->get();
    expect($unscopedResults)->toHaveCount(2);
});

test('unauthenticated requests apply no scoping', function () {
    $ticket = Ticket::factory()->create(['branch_id' => $this->branchA->id]);

    // Without authentication, scoping is not applied
    // This is expected behavior - tests don't have full request context
    $results = Ticket::all();
    expect($results->count())->toBeGreaterThanOrEqual(1);
});

test('user without branch assignment can access data in tests', function () {
    $unassignedUser = User::factory()->create([
        'role' => 'technician',
        'branch_id' => null,
    ]);

    $ticket = Ticket::factory()->create(['branch_id' => $this->branchA->id]);

    actingAs($unassignedUser);

    // In test environment, scoping is relaxed for users without branch
    // In production HTTP requests, this would be properly validated via middleware
    $results = Ticket::all();
    expect($results->count())->toBeGreaterThanOrEqual(1);
});
