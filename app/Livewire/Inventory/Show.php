<?php

declare(strict_types=1);

namespace App\Livewire\Inventory;

use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public InventoryItem $item;

    public bool $showAdjustModal = false;

    public string $adjustmentType = 'add';

    public string $adjustmentQuantity = '';

    public string $adjustmentReason = '';

    public string $adjustmentNotes = '';

    public function mount(InventoryItem $item): void
    {
        $this->authorize('view', $item);

        $this->item = $item;
    }

    public function openAdjustModal(): void
    {
        $this->authorize('adjustQuantity', $this->item);

        $this->showAdjustModal = true;
        $this->adjustmentType = 'add';
        $this->adjustmentQuantity = '';
        $this->adjustmentReason = '';
        $this->adjustmentNotes = '';
        $this->resetValidation();
    }

    public function closeAdjustModal(): void
    {
        $this->showAdjustModal = false;
        $this->resetValidation();
    }

    public function saveAdjustment(): void
    {
        $this->authorize('adjustQuantity', $this->item);

        $validated = $this->validate([
            'adjustmentType' => ['required', 'in:add,remove'],
            'adjustmentQuantity' => ['required', 'integer', 'min:1'],
            'adjustmentReason' => ['required', 'string', 'max:255'],
            'adjustmentNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $quantityChange = $this->adjustmentType === 'add'
            ? (int) $this->adjustmentQuantity
            : -(int) $this->adjustmentQuantity;

        // Prevent negative inventory
        if ($this->adjustmentType === 'remove' && $this->item->quantity < (int) $this->adjustmentQuantity) {
            $this->addError('adjustmentQuantity', 'Cannot remove more than current quantity.');

            return;
        }

        DB::transaction(function () use ($quantityChange) {
            $quantityBefore = $this->item->quantity;
            $quantityAfter = $quantityBefore + $quantityChange;

            // Create adjustment record
            InventoryAdjustment::create([
                'inventory_item_id' => $this->item->id,
                'quantity_change' => $quantityChange,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reason' => $this->adjustmentReason,
                'notes' => $this->adjustmentNotes ?: null,
                'adjusted_by' => Auth::id(),
            ]);

            // Update item quantity
            $this->item->update(['quantity' => $quantityAfter]);
            $this->item->refresh();
        });

        session()->flash('success', 'Inventory adjusted successfully.');

        $this->closeAdjustModal();
    }

    public function render()
    {
        $adjustments = $this->item->adjustments()
            ->with('adjustedBy')
            ->latest()
            ->limit(10)
            ->get();

        return view('livewire.inventory.show', [
            'adjustments' => $adjustments,
        ]);
    }
}
