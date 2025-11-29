<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SmsDeliveryLog;
use App\Models\SmsTemplate;
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
     * Send SMS using a template.
     */
    public function sendFromTemplate(string $templateKey, array $variables, string|array $phones, ?object $notifiable = null): bool
    {
        $template = SmsTemplate::findByKey($templateKey);

        if (! $template) {
            Log::warning('SMS template not found', ['key' => $templateKey]);

            return false;
        }

        $message = $template->render($variables);

        $phones = is_array($phones) ? $phones : [$phones];

        return $this->sendBulk($phones, $message, $notifiable, "template:{$templateKey}");
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
            // Calculate SMS segments (160 chars per segment for GSM-7, 70 for Unicode)
            $segments = $this->calculateSegments($message);

            return SmsDeliveryLog::create([
                'notifiable_type' => $notifiable ? get_class($notifiable) : null,
                'notifiable_id' => $notifiable?->id ?? null,
                'phone' => $phone,
                'message' => $message,
                'notification_type' => $notificationType,
                'status' => 'pending',
                'segments' => $segments,
            ]);
        });

        try {

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->apiUrl}", [
                'from' => $this->senderId,
                'body' => $message,
                'to' =>  $phones,
                'is_scheduled' => false,
                'flash' => false,
                'campaign_name' => uniqid('RepairDesk_'),
            ]);



            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('TextTango SMS API response', ['response' => $responseData]);

                $trackingId = $responseData['data']['tracking_id'] ?? null;

                // Mark all logs as sent and store external_id
                $logs->each(function ($log) use ($responseData, $trackingId) {
                    // Use tracking_id as external_id for TextTango
                    if ($trackingId) {
                        $log->external_id = $trackingId;
                        $log->save();
                    }

                    // Calculate and set cost
                    $cost = $log->calculateCost();
                    $log->update(['cost' => $cost]);

                    // Pass the response data array to markAsSent
                    $log->markAsSent($responseData ?? []);
                });

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
     * Retry a failed SMS message.
     */
    public function retrySms(SmsDeliveryLog $log): bool
    {
        // Check if retry is allowed
        if (! $log->canRetry()) {
            Log::warning('SMS retry not allowed', [
                'log_id' => $log->id,
                'retry_count' => $log->retry_count,
                'max_retries' => $log->max_retries,
            ]);

            return false;
        }

        if (empty($this->apiKey)) {
            Log::warning('TextTango API key not configured');

            return false;
        }

        // Update retry tracking before attempting
        $log->update([
            'retry_count' => $log->retry_count + 1,
            'last_retry_at' => now(),
            'next_retry_at' => null,
            'status' => 'pending',
            'error_message' => null,
        ]);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl, [
                'to' => $log->phone,
                'from' => $this->senderId,
                'body' => $log->message,
                'campaign_name' => uniqid('RepairDesk_Retry_'),
                'is_scheduled' => false,
                'is_scheduled_datetime' => null,
                'flash' => false,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $trackingId = $responseData['data']['tracking_id'] ?? null;

                if ($trackingId) {
                    $log->external_id = $trackingId;
                }

                $cost = $log->calculateCost();
                $log->update(['cost' => $cost]);
                $log->markAsSent($responseData ?? []);

                Log::info('SMS retry successful', [
                    'log_id' => $log->id,
                    'retry_count' => $log->retry_count,
                ]);

                return true;
            }

            $errorMessage = $response->body();
            $log->markAsFailed($errorMessage);

            // Schedule next retry if possible
            if ($log->retry_count < $log->max_retries) {
                $nextDelayMinutes = 2 ** $log->retry_count;
                $log->update([
                    'next_retry_at' => now()->addMinutes($nextDelayMinutes),
                ]);

                Log::info('SMS retry failed, scheduled next attempt', [
                    'log_id' => $log->id,
                    'retry_count' => $log->retry_count,
                    'next_retry_at' => $log->next_retry_at,
                ]);
            }

            return false;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $log->markAsFailed($errorMessage);

            // Schedule next retry if possible
            if ($log->retry_count < $log->max_retries) {
                $nextDelayMinutes = 2 ** $log->retry_count;
                $log->update([
                    'next_retry_at' => now()->addMinutes($nextDelayMinutes),
                ]);
            }

            Log::error('SMS retry exception', [
                'log_id' => $log->id,
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

    /**
     * Calculate SMS segments based on message length.
     * GSM-7 encoding: 160 chars per segment (single), 153 per segment (multi)
     * Unicode: 70 chars per segment (single), 67 per segment (multi)
     */
    public function calculateSegments(string $message): int
    {
        $length = mb_strlen($message);

        // Check if message contains Unicode characters
        $isUnicode = mb_strlen($message, 'UTF-8') !== mb_strlen($message);

        if ($isUnicode) {
            // Unicode (UCS-2) encoding
            if ($length <= 70) {
                return 1;
            }

            return (int) ceil($length / 67);
        }

        // GSM-7 encoding
        if ($length <= 160) {
            return 1;
        }

        return (int) ceil($length / 153);
    }
}
