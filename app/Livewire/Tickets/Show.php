<?php

declare(strict_types=1);

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Ticket $ticket;

    public function mount(Ticket $ticket): void
    {
        $this->authorize('view', $ticket);

        $this->ticket = $ticket->load([
            'customer',
            'device',
            'assignedTo',
            'createdBy',
            'notes.user',
            'parts.inventoryItem',
            'invoice.payments',
        ]);
    }

    public function render(): View
    {
        return view('livewire.tickets.show', [
            'title' => "Ticket #{$this->ticket->ticket_number}",
        ]);
    }
}
