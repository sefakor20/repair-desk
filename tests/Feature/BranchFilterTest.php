<?php

declare(strict_types=1);

use App\Models\{User, Branch, Ticket, InventoryItem, PosSale};
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->branches = Branch::factory()->count(3)->create();
});

dataset('branchModules', [
    [\App\Livewire\Tickets\Index::class, Ticket::class, 'tickets'],
    [\App\Livewire\Inventory\Index::class, InventoryItem::class, 'items'],
    [\App\Livewire\Pos\Index::class, PosSale::class, 'sales'],
    [\App\Livewire\Reports\Index::class, null, null], // Reports is aggregate, test filter UI
]);

it('shows branch filter dropdown in all modules', function (string $component, $modelClass, $dataKey) {
    actingAs($this->user);

    Livewire::test($component)
        ->assertSee('All Branches');
})->with('branchModules');

it('filters by branch in tickets, inventory, and pos', function (string $component, $modelClass, $dataKey) {
    if (!$modelClass) {
        $this->markTestSkipped('Not applicable for reports');
    }

    $branch = $this->branches->first();
    $otherBranch = $this->branches->last();

    // Create identifiable records so we can assert which appear after filtering
    if ($modelClass === \App\Models\Ticket::class) {
        $t1 = $modelClass::factory()->create(['branch_id' => $branch->id, 'ticket_number' => 'TICKET-A']);
        $t2 = $modelClass::factory()->create(['branch_id' => $otherBranch->id, 'ticket_number' => 'TICKET-B']);

        actingAs($this->user);

        Livewire::test($component)
            ->set('branchFilter', $branch->id)
            ->assertSee('TICKET-A')
            ->assertDontSee('TICKET-B');

        return;
    }

    if ($modelClass === \App\Models\InventoryItem::class) {
        $i1 = $modelClass::factory()->create(['branch_id' => $branch->id, 'name' => 'Item A']);
        $i2 = $modelClass::factory()->create(['branch_id' => $otherBranch->id, 'name' => 'Item B']);

        actingAs($this->user);

        Livewire::test($component)
            ->set('branchFilter', $branch->id)
            ->assertSee('Item A')
            ->assertDontSee('Item B');

        return;
    }

    if ($modelClass === \App\Models\PosSale::class) {
        $s1 = $modelClass::factory()->create(['branch_id' => $branch->id, 'sale_number' => 'SALE-A']);
        $s2 = $modelClass::factory()->create(['branch_id' => $otherBranch->id, 'sale_number' => 'SALE-B']);

        actingAs($this->user);

        Livewire::test($component)
            ->set('branchFilter', $branch->id)
            ->assertSee('SALE-A')
            ->assertDontSee('SALE-B');

        return;
    }
})->with('branchModules');
