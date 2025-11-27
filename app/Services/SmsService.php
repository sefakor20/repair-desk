<?php

declare(strict_types=1);

namespace App\Services;

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
    public function send(string $phone, string $message): bool
    {
        return $this->sendBulk([$phone], $message);
    }

    /**
     * Send SMS to multiple recipients.
     */
    public function sendBulk(array $phones, string $message): bool
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
                Log::info('SMS sent successfully', [
                    'recipients' => count($phones),
                    'response' => $response->json(),
                ]);

                return true;
            }

            Log::error('Failed to send SMS', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('SMS send exception', [
                'error' => $e->getMessage(),
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
