<?php

declare(strict_types=1);

namespace App\Livewire\Pos;

use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Models\{PosReturn, PosReturnItem, PosSale};
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\{Computed, Validate};
use Livewire\Component;

class ProcessReturn extends Component
{
    public PosSale $sale;

    #[Validate('required|string')]
    public string $returnReason = '';

    #[Validate('nullable|string|max:500')]
    public ?string $returnNotes = null;

    public array $selectedItems = [];

    public bool $autoApprove = true;

    public bool $restoreInventory = true;

    #[Validate('required|numeric|min:0')]
    public float $restockingFeePercentage = 0;

    public function mount(PosSale $sale): void
    {
        $this->sale = $sale->load(['items.inventoryItem', 'customer', 'returnPolicy']);

        // Initialize selected items with all items from the sale
        foreach ($this->sale->items as $item) {
            $this->selectedItems[$item->id] = [
                'selected' => false,
                'quantity' => 0,
                'max_quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'condition' => 'good',
                'notes' => '',
            ];
        }

        // Set default restocking fee from return reason
        if ($this->sale->returnPolicy) {
            $this->restockingFeePercentage = (float) $this->sale->returnPolicy->restocking_fee_percentage;
        }
    }

    #[Computed]
    public function returnableItems()
    {
        return $this->sale->items->filter(function ($item) {
            // Check if item has been previously returned
            $alreadyReturned = PosReturnItem::where('original_sale_item_id', $item->id)
                ->sum('quantity_returned');

            return $item->quantity > $alreadyReturned;
        });
    }

    #[Computed]
    public function subtotalReturned(): float
    {
        $total = 0;
        foreach ($this->selectedItems as $itemId => $data) {
            if ($data['selected'] && $data['quantity'] > 0) {
                $total += $data['quantity'] * $data['unit_price'];
            }
        }

        return $total;
    }

    #[Computed]
    public function taxReturned(): float
    {
        return $this->subtotalReturned * ((float) $this->sale->tax_rate / 100);
    }

    #[Computed]
    public function restockingFee(): float
    {
        if ($this->restockingFeePercentage <= 0) {
            return 0;
        }

        return ($this->subtotalReturned + $this->taxReturned) * ($this->restockingFeePercentage / 100);
    }

    #[Computed]
    public function totalRefund(): float
    {
        return ($this->subtotalReturned + $this->taxReturned) - $this->restockingFee;
    }

    public function updatedReturnReason(): void
    {
        // Update restocking fee based on return reason
        $reason = ReturnReason::from($this->returnReason);
        if ($reason->requiresRestockingFee()) {
            // Use return policy if available, otherwise use a default 10%
            $this->restockingFeePercentage = $this->sale->returnPolicy
                ? (float) $this->sale->returnPolicy->restocking_fee_percentage
                : 10.00;
        } else {
            $this->restockingFeePercentage = 0;
        }
    }

    public function processReturn(): void
    {
        $this->validate();

        // Check if any items are selected
        $hasSelectedItems = collect($this->selectedItems)->contains('selected', true);
        if (! $hasSelectedItems) {
            $this->addError('selectedItems', 'Please select at least one item to return.');

            return;
        }

        // Check if sale can be returned
        if (! $this->sale->canBeReturned()) {
            $this->addError('sale', 'This sale is outside the return window.');

            return;
        }

        DB::transaction(function () {
            // Create the return
            $return = PosReturn::create([
                'return_number' => PosReturn::generateReturnNumber(),
                'original_sale_id' => $this->sale->id,
                'customer_id' => $this->sale->customer_id,
                'processed_by' => auth()->id(),
                'shift_id' => session('current_shift_id'),
                'return_reason' => $this->returnReason,
                'return_notes' => $this->returnNotes,
                'status' => $this->autoApprove ? ReturnStatus::Approved : ReturnStatus::Pending,
                'subtotal_returned' => $this->subtotalReturned,
                'tax_returned' => $this->taxReturned,
                'restocking_fee' => $this->restockingFee,
                'total_refund_amount' => $this->totalRefund,
                'refund_method' => $this->sale->payment_method->value,
                'refund_reference' => null,
                'refunded_at' => $this->autoApprove ? now() : null,
                'inventory_restored' => false,
                'return_date' => now(),
            ]);

            // Create return items
            foreach ($this->selectedItems as $itemId => $data) {
                if ($data['selected'] && $data['quantity'] > 0) {
                    $saleItem = $this->sale->items->find($itemId);

                    PosReturnItem::create([
                        'pos_return_id' => $return->id,
                        'original_sale_item_id' => $saleItem->id,
                        'inventory_item_id' => $saleItem->inventory_item_id,
                        'quantity_returned' => $data['quantity'],
                        'unit_price' => $data['unit_price'],
                        'subtotal' => $data['quantity'] * $data['unit_price'],
                        'line_refund_amount' => $data['quantity'] * $data['unit_price'],
                        'item_condition' => $data['condition'],
                        'item_notes' => $data['notes'],
                    ]);
                }
            }

            // Restore inventory if needed
            if ($this->restoreInventory && $this->autoApprove) {
                $return->restoreInventory();
            }
        });

        session()->flash('success', 'Return processed successfully! Refund amount: ' . format_currency($this->totalRefund));
        $this->redirect(route('pos.returns.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.pos.process-return');
    }
}
