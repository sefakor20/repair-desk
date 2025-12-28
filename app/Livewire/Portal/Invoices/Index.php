<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Invoices;

use App\Models\Customer;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\{Attributes\Url, Component};
use Livewire\WithPagination;

#[Layout('components.layouts.portal-fullpage')]
class Index extends Component
{
    use WithPagination;

    public Customer $customer;

    #[Url(as: 'status')]
    public string $filterStatus = 'all';

    #[Url(as: 'search')]
    public string $search = '';

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;

        // Ensure customer has a portal access token
        if (! $customer->portal_access_token) {
            $customer->generatePortalAccessToken();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterStatus = 'all';
        $this->search = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $invoices = $this->customer->invoices()
            ->with(['ticket.device', 'payments'])
            ->when($this->filterStatus !== 'all', function ($query): void {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->search, function ($query): void {
                $query->where(function ($q): void {
                    $q->where('invoice_number', 'like', "%{$this->search}%")
                        ->orWhereHas('ticket', function ($ticketQuery): void {
                            $ticketQuery->where('ticket_number', 'like', "%{$this->search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.portal.invoices.index', [
            'invoices' => $invoices,
        ]);
    }
}
