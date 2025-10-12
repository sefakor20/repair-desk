<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\InventoryItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public InventoryItem $item,
        public string $alertType = 'low',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->alertType) {
            'critical' => '🔴 Critical: '
                . $this->item->name . ' is critically low on stock',
            'out' => '⛔ Alert: '
                . $this->item->name . ' is out of stock',
            default => '⚠️ Warning: '
                . $this->item->name . ' is running low on stock',
        };

        $message = (new MailMessage())
            ->subject($subject)
            ->greeting('Low Stock Alert')
            ->line("The inventory item **{$this->item->name}** requires your attention.");

        $message->line('**Current Stock Details:**')
            ->line("- SKU: {$this->item->sku}")
            ->line("- Current Quantity: {$this->item->quantity}")
            ->line("- Reorder Level: {$this->item->reorder_level}")
            ->line("- Selling Price: " . format_currency($this->item->selling_price));

        if ($this->alertType === 'out') {
            $message->line('⛔ **This item is OUT OF STOCK and cannot be sold.**');
        } elseif ($this->alertType === 'critical') {
            $message->line('🔴 **Stock is critically low. Immediate action required!**');
        }

        $message->action('View Inventory', route('inventory.show', $this->item))
            ->line('Please reorder this item as soon as possible to avoid stockouts.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'item_sku' => $this->item->sku,
            'current_quantity' => $this->item->quantity,
            'reorder_level' => $this->item->reorder_level,
            'alert_type' => $this->alertType,
            'selling_price' => $this->item->selling_price,
            'category' => $this->item->category,
        ];
    }
}
