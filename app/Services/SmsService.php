<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SmsDeliveryLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SmsService
{
    private string $apiKey;

    private string $senderId;

    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.texttango.api_key') ?? '';
        $this->senderId = config('services.texttango.sender_id') ?? 'RepairDesk';
        $this->apiUrl = config('services.texttango.url') ?? '';
    }

    /**
     * Send SMS to a single recipient.
     */
    public function send(string $phone, string $message, ?object $notifiable = null, ?string $notificationType = null): bool
    {
        return $this->sendBulk([$phone], $message, $notifiable, $notificationType);
    }

    /**
     * Send SMS to multiple recipients.
     */
    public function sendBulk(array $phones, string $message, ?object $notifiable = null, ?string $notificationType = null): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('TextTango API key not configured');

            return false;
        }

        if (empty($phones)) {
            return false;
        }

        // Format phone numbers (remove spaces, dashes, etc.)
        $phones = array_map(function ($phone) {
            return preg_replace('/[^0-9+]/', '', $phone);
        }, $phones);

        // Create delivery logs for each phone number
        $logs = collect($phones)->map(function ($phone) use ($message, $notifiable, $notificationType) {
            return SmsDeliveryLog::create([
                'notifiable_type' => $notifiable ? get_class($notifiable) : null,
                'notifiable_id' => $notifiable?->id ?? null,
                'phone' => $phone,
                'message' => $message,
                'notification_type' => $notificationType,
                'status' => 'pending',
            ]);
        });

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl, [
                'to' => implode(',', $phones),
                'from' => $this->senderId,
                'body' => $message,
                'campaign_name' => uniqid('RepairDesk_'),
                'is_scheduled' => false,
                'is_scheduled_datetime' => null,
                'flash' => false,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                // Mark all logs as sent
                $logs->each(function ($log) use ($responseData) {
                    $log->markAsSent($responseData);
                });

                Log::info('SMS sent successfully', [
                    'recipients' => count($phones),
                    'response' => $responseData,
                ]);

                return true;
            }

            $errorMessage = $response->body();

            // Mark all logs as failed
            $logs->each(function ($log) use ($errorMessage) {
                $log->markAsFailed($errorMessage);
            });

            Log::error('Failed to send SMS', [
                'status' => $response->status(),
                'body' => $errorMessage,
            ]);

            return false;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            // Mark all logs as failed
            $logs->each(function ($log) use ($errorMessage) {
                $log->markAsFailed($errorMessage);
            });

            Log::error('SMS send exception', [
                'error' => $errorMessage,
            ]);

            return false;
        }
    }

    /**
     * Check if SMS service is configured and enabled.
     */
    public function isEnabled(): bool
    {
        return ! empty($this->apiKey) && ! empty($this->apiUrl);
    }
}
