<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Livewire\Attributes\{Layout, Url};
use Livewire\{Component, WithPagination};

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    public ?string $deletingInvoiceId = null;

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $invoices = Invoice::query()
            ->with(['customer', 'ticket'])
            ->when($this->search, function ($query): void {
                $query->where(function ($q): void {
                    $q->where('invoice_number', 'like', "%{$this->search}%")
                        ->orWhereHas('customer', fn($q) => $q->where('first_name', 'like', "%{$this->search}%")
                            ->orWhere('last_name', 'like', "%{$this->search}%"))
                        ->orWhereHas('ticket', fn($q) => $q->where('ticket_number', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status, function ($query): void {
                $query->where('status', InvoiceStatus::from($this->status));
            })
            ->latest()
            ->paginate(15);

        return view('livewire.invoices.index', [
            'invoices' => $invoices,
        ]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function confirmDelete(string $invoiceId): void
    {
        $this->deletingInvoiceId = $invoiceId;
    }

    public function delete(): void
    {
        if (!$this->deletingInvoiceId) {
            return;
        }

        $invoice = Invoice::findOrFail($this->deletingInvoiceId);

        $this->authorize('delete', $invoice);

        $invoice->delete();

        $this->deletingInvoiceId = null;

        session()->flash('success', 'Invoice deleted successfully.');
    }

    public function cancelDelete(): void
    {
        $this->deletingInvoiceId = null;
    }
}
