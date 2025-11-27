<?php

declare(strict_types=1);

namespace App\Channels;

use App\Models\Customer;
use App\Notifications\RepairCompleted;
use App\Notifications\TicketStatusChanged;
use App\Services\SmsService;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function __construct(
        private SmsService $smsService,
    ) {
        //
    }

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $this->smsService->isEnabled()) {
            return;
        }

        $phone = $notifiable->phone ?? null;

        if (! $phone) {
            return;
        }

        // Check customer SMS preferences
        if ($notifiable instanceof Customer) {
            $preferences = $notifiable->preferences;

            if (! $preferences || ! $preferences->sms_enabled) {
                return;
            }

            // Check specific notification type preferences
            if ($notification instanceof TicketStatusChanged && ! $preferences->sms_ticket_updates) {
                return;
            }

            if ($notification instanceof RepairCompleted && ! $preferences->sms_repair_completed) {
                return;
            }
        }

        $message = $notification->toSms($notifiable);
        $notificationType = get_class($notification);

        $this->smsService->send($phone, $message, $notifiable, $notificationType);
    }
}
