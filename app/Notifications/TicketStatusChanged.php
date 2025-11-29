<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $oldStatus,
        public string $newStatus,
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        // Add SMS channel if phone number exists
        if ($notifiable->phone) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('portal.tickets.show', [
            'customer' => $notifiable->id,
            'token' => $notifiable->portal_access_token ?? $notifiable->generatePortalAccessToken(),
            'ticket' => $this->ticket->id,
        ]);

        return (new MailMessage())
            ->subject('Ticket Status Update - #' . $this->ticket->ticket_number)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('Your repair ticket status has been updated.')
            ->line('**Ticket #:** ' . $this->ticket->ticket_number)
            ->line('**Device:** ' . $this->ticket->device->fullName)
            ->line('**Previous Status:** ' . ucfirst(str_replace('_', ' ', $this->oldStatus)))
            ->line('**New Status:** ' . ucfirst(str_replace('_', ' ', $this->newStatus)))
            ->when($this->ticket->assigned_technician_id, function ($mail) {
                return $mail->line('**Technician:** ' . $this->ticket->assignedTechnician->name);
            })
            ->action('View Ticket Details', $url)
            ->line('Thank you for choosing ' . config('app.name') . '!');
    }

    /**
     * Get the SMS message.
     */
    public function toSms(object $notifiable): string
    {
        return sprintf(
            "%s: Your ticket #%s status changed from %s to %s. Device: %s. View details at: %s",
            config('app.name'),
            $this->ticket->ticket_number,
            ucfirst(str_replace('_', ' ', $this->oldStatus)),
            ucfirst(str_replace('_', ' ', $this->newStatus)),
            $this->ticket->device->fullName,
            url('/portal'),
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}
