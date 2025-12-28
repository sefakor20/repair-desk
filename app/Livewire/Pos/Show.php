<?php

declare(strict_types=1);

namespace App\Livewire\Pos;

use App\Enums\PosSaleStatus;
use App\Models\{InventoryItem, PosSale};
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public PosSale $sale;
    public bool $showRefundModal = false;
    public string $refundReason = '';

    public function mount(PosSale $sale): void
    {
        $this->authorize('view', $sale);
        $this->sale = $sale->load(['customer', 'soldBy', 'items.inventoryItem']);
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.pos.show');
    }

    public function openRefundModal(): void
    {
        $this->authorize('refund', $this->sale);

        if ($this->sale->status === PosSaleStatus::Refunded) {
            $this->addError('refund', 'This sale has already been refunded.');
            return;
        }

        $this->refundReason = '';
        $this->showRefundModal = true;
    }

    public function closeRefundModal(): void
    {
        $this->showRefundModal = false;
        $this->refundReason = '';
    }

    public function processRefund(): void
    {
        $this->authorize('refund', $this->sale);

        if ($this->sale->status === PosSaleStatus::Refunded) {
            $this->addError('refund', 'This sale has already been refunded.');
            return;
        }

        $validated = $this->validate([
            'refundReason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated): void {
            // Update sale status
            $this->sale->update([
                'status' => PosSaleStatus::Refunded,
                'notes' => ($this->sale->notes ? $this->sale->notes . "\n\n" : '')
                    . 'REFUND: ' . $validated['refundReason'] . ' (Refunded on ' . now()->format('Y-m-d H:i') . ')',
            ]);

            // Restore inventory quantities
            foreach ($this->sale->items as $item) {
                $inventoryItem = InventoryItem::find($item->inventory_item_id);
                if ($inventoryItem) {
                    $inventoryItem->increment('quantity', $item->quantity);
                }
            }
        });

        $this->showRefundModal = false;
        $this->sale->refresh();

        session()->flash('success', 'Sale refunded successfully. Inventory has been restored.');
    }
}
