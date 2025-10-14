<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Tickets;

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
        $tickets = $this->customer->tickets()
            ->with(['device', 'assignedTo'])
            ->when($this->filterStatus !== 'all', function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('ticket_number', 'like', "%{$this->search}%")
                        ->orWhere('problem_description', 'like', "%{$this->search}%")
                        ->orWhereHas('device', function ($deviceQuery) {
                            $deviceQuery->where('brand', 'like', "%{$this->search}%")
                                ->orWhere('model', 'like', "%{$this->search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.portal.tickets.index', [
            'tickets' => $tickets,
        ]);
    }
}
