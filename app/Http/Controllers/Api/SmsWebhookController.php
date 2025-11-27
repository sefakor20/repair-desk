<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsDeliveryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsWebhookController extends Controller
{
    /**
     * Handle TextTango SMS delivery status webhook.
     */
    public function handleDeliveryStatus(Request $request)
    {
        // Verify webhook signature if secret is configured
        if (config('services.texttango.webhook_secret')) {
            if (! $this->verifyWebhookSignature($request)) {
                Log::warning('Invalid webhook signature', [
                    'ip' => $request->ip(),
                    'signature' => $request->header('X-TextTango-Signature'),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 401);
            }
        }

        // Log the incoming webhook for debugging
        Log::info('SMS Webhook Received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Validate required fields
        $validated = $request->validate([
            'message_id' => 'required|string',
            'status' => 'required|string|in:sent,delivered,failed,pending',
            'phone' => 'sometimes|string',
            'error_message' => 'nullable|string',
            'delivered_at' => 'nullable|date',
        ]);

        // Find the SMS delivery log by external_id (message_id from TextTango)
        $log = SmsDeliveryLog::where('external_id', $validated['message_id'])->first();

        if (! $log) {
            Log::warning('SMS Delivery Log not found', ['message_id' => $validated['message_id']]);

            return response()->json([
                'success' => false,
                'message' => 'SMS delivery log not found',
            ], 404);
        }

        // Update the delivery status
        $log->status = $validated['status'];

        if ($validated['status'] === 'sent' || $validated['status'] === 'delivered') {
            $log->markAsSent();

            // If delivered_at is provided, update it
            if (isset($validated['delivered_at'])) {
                $log->sent_at = $validated['delivered_at'];
                $log->save();
            }
        } elseif ($validated['status'] === 'failed') {
            $log->markAsFailed($validated['error_message'] ?? 'Delivery failed');
        }

        Log::info('SMS Delivery Status Updated', [
            'log_id' => $log->id,
            'status' => $validated['status'],
            'phone' => $log->phone_number,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery status updated',
        ]);
    }

    /**
     * Verify the webhook signature using HMAC.
     */
    protected function verifyWebhookSignature(Request $request): bool
    {
        $signature = $request->header('X-TextTango-Signature');

        if (! $signature) {
            return false;
        }

        $secret = config('services.texttango.webhook_secret');
        $payload = $request->getContent();

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
