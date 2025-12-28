<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Collection $lowStockItems,
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

        // Add SMS channel if phone number exists and user is admin
        if ($notifiable->phone && $notifiable->role->value === 'admin') {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->lowStockItems->count();
        $mail = (new MailMessage())
            ->subject('Low Stock Alert - ' . $count . ' Item' . ($count > 1 ? 's' : ''))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The following items are running low on stock:')
            ->line('');

        foreach ($this->lowStockItems as $item) {
            $mail->line('**' . $item->name . '** (SKU: ' . $item->sku . ')');
            $mail->line('Current Stock: ' . $item->quantity . ' | Reorder Level: ' . $item->reorder_level);
            $mail->line('');
        }

        return $mail->action('View Inventory', route('inventory.index'))
            ->line('Please reorder these items to maintain adequate stock levels.');
    }

    /**
     * Get the SMS message.
     */
    public function toSms(object $notifiable): string
    {
        $count = $this->lowStockItems->count();
        $itemsList = $this->lowStockItems->take(3)->pluck('name')->implode(', ');

        return sprintf(
            "%s: LOW STOCK ALERT! %d item%s running low: %s%s. Check inventory now.",
            config('app.name'),
            $count,
            $count > 1 ? 's' : '',
            $itemsList,
            $count > 3 ? ' and more' : '',
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
            'items_count' => $this->lowStockItems->count(),
            'items' => $this->lowStockItems->map(function ($item): array {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'quantity' => $item->quantity,
                    'reorder_level' => $item->reorder_level,
                ];
            })->toArray(),
        ];
    }
}
