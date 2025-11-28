<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Customer;
use App\Models\SmsCampaign;
use App\Services\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessSmsCampaign implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SmsCampaign $campaign,
        public int $batchSize = 50,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        // Mark campaign as sending if not already
        if ($this->campaign->status !== 'sending') {
            $this->campaign->markAsSending();
        }

        try {
            // Get recipients based on segment rules
            $recipients = $this->getRecipients();

            // Update total recipients if not set
            if ($this->campaign->total_recipients === 0) {
                $this->campaign->update(['total_recipients' => $recipients->count()]);
            }

            // Process recipients in chunks
            $recipients->chunk($this->batchSize)->each(function ($chunk) use ($smsService) {
                foreach ($chunk as $customer) {
                    try {
                        // Check if customer has SMS enabled
                        if (! $customer->canReceiveSms()) {
                            continue;
                        }

                        // Send SMS via service
                        $result = $smsService->send(
                            phone: $customer->phone,
                            message: $this->campaign->message,
                            notifiable: $customer,
                            notificationType: 'campaign',
                        );

                        // Link delivery log to campaign (result is bool, need to get the log)
                        if ($result) {
                            // Get the most recent delivery log for this customer
                            $log = $customer->smsDeliveryLogs()
                                ->where('message', $this->campaign->message)
                                ->latest()
                                ->first();

                            if ($log) {
                                $log->update(['campaign_id' => $this->campaign->id]);
                            }

                            $this->campaign->incrementSentCount();
                        } else {
                            $this->campaign->incrementFailedCount();
                        }
                    } catch (Exception $e) {
                        Log::error('Campaign SMS send failed', [
                            'campaign_id' => $this->campaign->id,
                            'customer_id' => $customer->id,
                            'error' => $e->getMessage(),
                        ]);

                        $this->campaign->incrementFailedCount();
                    }
                }
            });

            // Mark campaign as completed
            $this->campaign->markAsCompleted();

            Log::info('Campaign completed', [
                'campaign_id' => $this->campaign->id,
                'sent_count' => $this->campaign->sent_count,
                'failed_count' => $this->campaign->failed_count,
            ]);
        } catch (Exception $e) {
            Log::error('Campaign processing failed', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
            ]);

            // Keep status as sending so it can be retried manually
            throw $e;
        }
    }

    /**
     * Get recipients based on segment rules.
     */
    protected function getRecipients()
    {
        $query = Customer::query();

        $segmentRules = $this->campaign->segment_rules;

        if (! $segmentRules) {
            // Default to all customers with phone numbers
            return $query->whereNotNull('phone')->get();
        }

        $type = $segmentRules['type'] ?? 'all';

        return match ($type) {
            'all' => $query->whereNotNull('phone')->get(),
            'recent' => $query->whereNotNull('phone')
                ->where('created_at', '>=', now()->subDays($segmentRules['days'] ?? 30))
                ->get(),
            'active' => $query->whereNotNull('phone')
                ->whereHas('tickets', function ($q) {
                    $q->where('created_at', '>=', now()->subMonths(3));
                })
                ->get(),
            default => $query->whereNotNull('phone')->get(),
        };
    }
}
