<?php

declare(strict_types=1);

namespace App\Channels;

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

        $message = $notification->toSms($notifiable);

        $this->smsService->send($phone, $message);
    }
}
