<?php

declare(strict_types=1);

namespace App\Livewire\Tickets;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\{Layout, Url};
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Tickets'])]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $priorityFilter = '';

    #[Url]
    public string $assignedFilter = '';

    public function delete(Ticket $ticket): void
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        session()->flash('success', 'Ticket deleted successfully.');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAssignedFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'priorityFilter', 'assignedFilter']);
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.tickets.index', [
            'tickets' => Ticket::query()
                ->with(['customer', 'device', 'assignedTo'])
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('ticket_number', 'like', "%{$this->search}%")
                            ->orWhere('problem_description', 'like', "%{$this->search}%")
                            ->orWhereHas('customer', function ($customerQuery) {
                                $customerQuery->where('first_name', 'like', "%{$this->search}%")
                                    ->orWhere('last_name', 'like', "%{$this->search}%")
                                    ->orWhere('email', 'like', "%{$this->search}%");
                            });
                    });
                })
                ->when($this->statusFilter, function ($query) {
                    $query->where('status', $this->statusFilter);
                })
                ->when($this->priorityFilter, function ($query) {
                    $query->where('priority', $this->priorityFilter);
                })
                ->when($this->assignedFilter, function ($query) {
                    if ($this->assignedFilter === 'unassigned') {
                        $query->whereNull('assigned_to');
                    } else {
                        $query->where('assigned_to', $this->assignedFilter);
                    }
                })
                ->latest()
                ->paginate(15),
            'statuses' => TicketStatus::cases(),
            'priorities' => TicketPriority::cases(),
            'technicians' => User::whereIn('role', ['admin', 'manager', 'technician'])->get(),
        ])->with('Ticket', Ticket::class);
    }
}
