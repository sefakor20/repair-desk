<?php

declare(strict_types=1);

use App\Models\{Branch, Ticket, InventoryItem, PosSale, Invoice, Payment, User};
use App\Services\BranchContextService;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->branches = Branch::factory()->count(3)->create();
    $this->branchContext = app(BranchContextService::class);
});

describe('Branch Data Isolation', function (): void {
    it('prevents users from accessing data outside their branch', function (): void {
        $branch1 = $this->branches->get(0);
        $branch2 = $this->branches->get(1);

        $user1 = User::factory()->create(['branch_id' => $branch1->id]);
        $ticket1 = Ticket::factory()->create(['branch_id' => $branch1->id]);
        $ticket2 = Ticket::factory()->create(['branch_id' => $branch2->id]);

        actingAs($user1);

        $results = Ticket::all();
        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($ticket1->id);
    });

    it('scopes inventory items to user branch', function (): void {
        $branch1 = $this->branches->get(0);
        $branch2 = $this->branches->get(1);

        $user1 = User::factory()->create(['branch_id' => $branch1->id]);
        $item1 = InventoryItem::factory()->create(['branch_id' => $branch1->id]);
        $item2 = InventoryItem::factory()->create(['branch_id' => $branch2->id]);

        actingAs($user1);

        $results = InventoryItem::all();
        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($item1->id);
    });

    it('scopes POS sales to user branch', function (): void {
        $branch1 = $this->branches->get(0);
        $branch2 = $this->branches->get(1);

        $user1 = User::factory()->create(['branch_id' => $branch1->id]);
        $sale1 = PosSale::factory()->create(['branch_id' => $branch1->id]);
        $sale2 = PosSale::factory()->create(['branch_id' => $branch2->id]);

        actingAs($user1);

        $results = PosSale::all();
        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($sale1->id);
    });

    it('allows super admins without branch to see all data', function (): void {
        $branch1 = $this->branches->get(0);
        $branch2 = $this->branches->get(1);

        $admin = User::factory()->create(['role' => 'admin', 'branch_id' => null]);
        Ticket::factory()->create(['branch_id' => $branch1->id]);
        Ticket::factory()->create(['branch_id' => $branch2->id]);

        actingAs($admin);

        $results = Ticket::all();
        expect($results)->toHaveCount(2);
    });

    it('excludes unauthenticated users from queries', function (): void {
        $branch1 = $this->branches->get(0);
        Ticket::factory()->create(['branch_id' => $branch1->id]);

        $results = Ticket::all();
        expect($results)->toHaveCount(1);
    });

    it('scopes invoices to user branch', function (): void {
        $branch1 = $this->branches->get(0);
        $branch2 = $this->branches->get(1);

        $user1 = User::factory()->create(['branch_id' => $branch1->id]);
        $invoice1 = Invoice::factory()->create(['branch_id' => $branch1->id]);
        $invoice2 = Invoice::factory()->create(['branch_id' => $branch2->id]);

        actingAs($user1);

        $results = Invoice::all();
        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($invoice1->id);
    });

    it('scopes payments to user branch', function (): void {
        $branch1 = $this->branches->get(0);
        $branch2 = $this->branches->get(1);

        $user1 = User::factory()->create(['branch_id' => $branch1->id]);
        $payment1 = Payment::factory()->create(['branch_id' => $branch1->id]);
        $payment2 = Payment::factory()->create(['branch_id' => $branch2->id]);

        actingAs($user1);

        $results = Payment::all();
        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($payment1->id);
    });
});

describe('Branch Context Service', function (): void {
    it('provides current branch context for authenticated user', function (): void {
        $branch = $this->branches->first();
        $user = User::factory()->create(['branch_id' => $branch->id]);

        actingAs($user);

        $currentBranch = $this->branchContext->getCurrentBranch();
        expect($currentBranch?->id)->toBe($branch->id);
    });

    it('checks if user can access specific branch', function (): void {
        $branch1 = $this->branches->get(0);
        $branch2 = $this->branches->get(1);

        $user = User::factory()->create(['branch_id' => $branch1->id]);

        actingAs($user);

        expect($this->branchContext->canAccessBranch($branch1))->toBeTrue()
            ->and($this->branchContext->canAccessBranch($branch2))->toBeFalse();
    });

    it('returns accessible branches for super admin', function (): void {
        $admin = User::factory()->create(['role' => 'admin', 'branch_id' => null]);

        actingAs($admin);

        $accessible = $this->branchContext->getAccessibleBranches();
        expect($accessible)->toHaveCount(3);
    });

    it('returns only their branch for regular user', function (): void {
        $branch = $this->branches->first();
        $user = User::factory()->create(['branch_id' => $branch->id]);

        actingAs($user);

        $accessible = $this->branchContext->getAccessibleBranches();
        expect($accessible)->toHaveCount(1)
            ->and($accessible->first()->id)->toBe($branch->id);
    });
});

describe('User Branch Methods', function (): void {
    it('identifies super admins correctly', function (): void {
        $superAdmin = User::factory()->create(['role' => 'admin', 'branch_id' => null]);
        $branchAdmin = User::factory()->create(['role' => 'admin', 'branch_id' => $this->branches->first()->id]);

        expect($superAdmin->isSuperAdmin())->toBeTrue()
            ->and($branchAdmin->isSuperAdmin())->toBeFalse();
    });

    it('checks if user can manage branch', function (): void {
        $branch1 = $this->branches->get(0);
        $branch2 = $this->branches->get(1);

        $user = User::factory()->create(['branch_id' => $branch1->id]);
        $superAdmin = User::factory()->create(['role' => 'admin', 'branch_id' => null]);

        expect($user->canManageBranch($branch1))->toBeTrue()
            ->and($user->canManageBranch($branch2))->toBeFalse()
            ->and($superAdmin->canManageBranch($branch1))->toBeTrue();
    });
});
