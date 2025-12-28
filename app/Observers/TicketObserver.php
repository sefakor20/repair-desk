<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Notifications\{RepairCompleted, TicketStatusChanged};

class TicketObserver
{
    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        // Check if status has changed
        if ($ticket->wasChanged('status')) {
            $oldStatus = $ticket->getOriginal('status');
            $newStatus = $ticket->status->value;

            // Convert old status enum to string if it's an enum
            if ($oldStatus instanceof TicketStatus) {
                $oldStatus = $oldStatus->value;
            }

            // Notify customer about status change
            if ($ticket->customer && $ticket->customer->email) {
                $ticket->customer->notify(
                    new TicketStatusChanged($ticket, $oldStatus, $newStatus),
                );
            }

            // Send repair completed notification when status is completed
            if ($ticket->status === TicketStatus::Completed && ($ticket->customer && $ticket->customer->email)) {
                $ticket->customer->notify(new RepairCompleted($ticket));
            }
        }
    }
}
