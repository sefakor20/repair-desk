<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RepairCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
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

        $invoice = $this->ticket->invoice;

        return (new MailMessage())
            ->subject('Repair Completed - #' . $this->ticket->ticket_number)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('Great news! Your device repair has been completed.')
            ->line('**Ticket #:** ' . $this->ticket->ticket_number)
            ->line('**Device:** ' . $this->ticket->device->fullName)
            ->line('**Completed On:** ' . $this->ticket->actual_completion?->format('M d, Y'))
            ->when($invoice, function ($mail) use ($invoice) {
                $balance = $invoice->balance_due;

                return $mail->line('**Invoice Total:** ' . config('shop.currency', 'GHS') . ' ' . number_format($invoice->total_amount, 2))
                    ->line('**Balance Due:** ' . config('shop.currency', 'GHS') . ' ' . number_format($balance, 2))
                    ->when($balance > 0, function ($m) {
                        return $m->line('Please arrange payment to collect your device.');
                    });
            })
            ->action('View Ticket & Invoice', $url)
            ->line('Your device is ready for pickup!')
            ->line('Thank you for choosing ' . config('app.name') . '!');
    }

    /**
     * Get the SMS message.
     */
    public function toSms(object $notifiable): string
    {
        $invoice = $this->ticket->invoice;
        $message = sprintf(
            "%s: Your repair is complete! Ticket #%s for %s is ready for pickup.",
            config('app.name'),
            $this->ticket->ticket_number,
            $this->ticket->device->fullName,
        );

        if ($invoice) {
            $balance = $invoice->balance_due;
            if ($balance > 0) {
                $message .= sprintf(
                    " Balance due: %s%.2f. View details: %s",
                    config('shop.currency', 'GHS'),
                    $balance,
                    url('/portal'),
                );
            }
        }

        return $message;
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
            'device' => $this->ticket->device->fullName,
        ];
    }
}
