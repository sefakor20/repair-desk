<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ProcessSmsAutomationTrigger;
use App\Models\SmsAutomationTrigger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SmsAutomationService
{
    /**
     * Trigger automation for a specific event.
     */
    public function triggerEvent(string $eventName, Model $model, array $eventData = []): void
    {
        $triggers = SmsAutomationTrigger::active()
            ->byEvent($eventName)
            ->with('smsTemplate')
            ->get();

        foreach ($triggers as $trigger) {
            // Skip if template is not active or approved
            if (!$trigger->smsTemplate?->is_active) {
                continue;
            }

            if ($trigger->smsTemplate->requires_approval && $trigger->smsTemplate->approval_status !== 'approved') {
                continue;
            }

            $this->scheduleOrSendTrigger($trigger, $model, $eventData);
        }
    }

    /**
     * Schedule or immediately send a trigger based on delay settings.
     */
    protected function scheduleOrSendTrigger(SmsAutomationTrigger $trigger, Model $model, array $eventData): void
    {
        $delayMinutes = $trigger->getDelayMinutes();

        if ($delayMinutes > 0) {
            // Schedule for later
            ProcessSmsAutomationTrigger::dispatch($trigger, $model, $eventData)
                ->delay(Carbon::now()->addMinutes($delayMinutes));
        } else {
            // Send immediately
            ProcessSmsAutomationTrigger::dispatch($trigger, $model, $eventData);
        }
    }

    /**
     * Handle ticket status change events.
     */
    public function handleTicketStatusChange(Model $ticket, string $oldStatus, string $newStatus): void
    {
        $eventData = [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'status' => $newStatus,
        ];

        $this->triggerEvent('ticket_status_changed', $ticket, $eventData);

        // Handle specific status change events
        match ($newStatus) {
            'completed' => $this->triggerEvent('repair_completed', $ticket, $eventData),
            'ready_for_pickup' => $this->triggerEvent('device_ready_pickup', $ticket, $eventData),
            default => null,
        };
    }

    /**
     * Handle ticket creation events.
     */
    public function handleTicketCreated(Model $ticket): void
    {
        $eventData = [
            'status' => $ticket->status ?? 'new',
        ];

        $this->triggerEvent('ticket_created', $ticket, $eventData);
    }

    /**
     * Handle appointment creation events.
     */
    public function handleAppointmentCreated(Model $appointment): void
    {
        $eventData = [
            'appointment_date' => $appointment->scheduled_at ?? null,
            'appointment_type' => $appointment->type ?? 'general',
        ];

        $this->triggerEvent('appointment_created', $appointment, $eventData);
    }

    /**
     * Handle payment received events.
     */
    public function handlePaymentReceived(Model $payment): void
    {
        $eventData = [
            'amount' => $payment->amount ?? 0,
            'payment_method' => $payment->method ?? 'unknown',
        ];

        $this->triggerEvent('payment_received', $payment, $eventData);
    }

    /**
     * Handle overdue payment events.
     */
    public function handlePaymentOverdue(Model $ticket): void
    {
        $eventData = [
            'days_overdue' => $ticket->days_overdue ?? 0,
            'amount_due' => $ticket->amount_due ?? 0,
        ];

        $this->triggerEvent('payment_overdue', $ticket, $eventData);
    }

    /**
     * Send appointment reminders.
     */
    public function sendAppointmentReminders(): void
    {
        // This would be called by a scheduled command
        // Find appointments scheduled for tomorrow and trigger reminders

        $triggers = SmsAutomationTrigger::active()
            ->byEvent('appointment_reminder')
            ->with('smsTemplate')
            ->get();

        foreach ($triggers as $trigger) {
            // Logic to find appointments that need reminders
            // This would depend on your appointment model structure
        }
    }
}
