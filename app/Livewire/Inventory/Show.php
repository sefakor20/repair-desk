<?php

declare(strict_types=1);

namespace App\Livewire\Inventory;

use App\Models\InventoryItem;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public InventoryItem $item;

    public function mount(InventoryItem $item): void
    {
        $this->authorize('view', $item);

        $this->item = $item;
    }

    public function render()
    {
        return view('livewire.inventory.show');
    }
}
