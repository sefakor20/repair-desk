<?php

declare(strict_types=1);

namespace App\Livewire\Portal\Tickets;

use App\Models\{Customer, Ticket};
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal-fullpage')]
class Show extends Component
{
    public Customer $customer;

    public Ticket $ticket;

    public function mount(Customer $customer, Ticket $ticket): void
    {
        // Ensure ticket belongs to customer
        if ($ticket->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to ticket');
        }

        $this->customer = $customer;
        $this->ticket = $ticket;

        // Ensure customer has a portal access token
        if (! $customer->portal_access_token) {
            $customer->generatePortalAccessToken();
        }
    }

    public function render(): View
    {
        $this->ticket->load([
            'device',
            'assignedTo',
            'createdBy',
            'parts',
            'notes.user',
            'invoice.payments',
        ]);

        return view('livewire.portal.tickets.show');
    }
}
