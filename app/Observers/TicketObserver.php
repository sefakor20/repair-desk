<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\TicketStatus;
use App\Jobs\ProcessSmsAutomationTrigger;
use App\Models\{SmsAutomationTrigger, Ticket};
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

            // Fire SMS automation triggers for status changes
            $this->fireSmsAutomationTriggers($ticket, 'ticket_status_changed', [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        }
    }

    /**
     * Fire SMS automation triggers for the given event.
     */
    protected function fireSmsAutomationTriggers(Ticket $ticket, string $event, array $data = []): void
    {
        $triggers = SmsAutomationTrigger::active()
            ->byEvent($event)
            ->with('smsTemplate')
            ->get();

        foreach ($triggers as $trigger) {
            // Check if trigger conditions are met
            if (!$trigger->conditionsMet($data)) {
                continue;
            }

            // Check if there are recipients for this trigger
            $recipients = $trigger->getRecipients($ticket);
            if (empty($recipients)) {
                continue;
            }

            // Queue the SMS job
            if ($trigger->getDelayMinutes() > 0) {
                ProcessSmsAutomationTrigger::dispatch($trigger, $ticket)
                    ->delay(now()->addMinutes($trigger->getDelayMinutes()));
            } else {
                ProcessSmsAutomationTrigger::dispatch($trigger, $ticket);
            }
        }
    }
}
