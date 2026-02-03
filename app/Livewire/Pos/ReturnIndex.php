<?php

declare(strict_types=1);

namespace App\Livewire\Pos;

use Livewire\Component;
use App\Models\PosReturn;
use App\Enums\ReturnStatus;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\{Computed, Url};

#[Layout('components.layouts.app')]
class ReturnIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function returns()
    {
        return PosReturn::query()
            ->with(['originalSale.customer', 'processedBy', 'items'])
            ->when($this->search, function ($query): void {
                $query->where(function ($q): void {
                    $q->where('return_number', 'like', "%{$this->search}%")
                        ->orWhereHas('originalSale', function ($sale): void {
                            $sale->where('sale_number', 'like', "%{$this->search}%");
                        })
                        ->orWhereHas('customer', function ($customer): void {
                            $customer->where('first_name', 'like', "%{$this->search}%")
                                ->orWhere('last_name', 'like', "%{$this->search}%")
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$this->search}%"]);
                        });
                });
            })
            ->when($this->status !== 'all', function ($query): void {
                $query->where('status', $this->status);
            })
            ->recent()
            ->paginate(15);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'pending_count' => PosReturn::where('status', ReturnStatus::Pending)->count(),
            'approved_count' => PosReturn::where('status', ReturnStatus::Approved)->count(),
            'completed_count' => PosReturn::where('status', ReturnStatus::Completed)->count(),
            'total_refunded' => PosReturn::where('status', ReturnStatus::Completed)
                ->sum('total_refund_amount'),
        ];
    }

    public function approveReturn(string $returnId): void
    {
        $return = PosReturn::findOrFail($returnId);

        if ($return->status !== ReturnStatus::Pending) {
            $this->addError('return', 'Only pending returns can be approved.');

            return;
        }

        $return->update([
            'status' => ReturnStatus::Approved,
            'refunded_at' => now(),
        ]);

        $return->restoreInventory();

        session()->flash('success', 'Return approved and inventory restored.');
    }

    public function rejectReturn(string $returnId): void
    {
        $return = PosReturn::findOrFail($returnId);

        if ($return->status !== ReturnStatus::Pending) {
            $this->addError('return', 'Only pending returns can be rejected.');

            return;
        }

        $return->update(['status' => ReturnStatus::Rejected]);

        session()->flash('success', 'Return rejected.');
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('livewire.pos.return-index');
    }
}
