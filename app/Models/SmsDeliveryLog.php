<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SmsDeliveryLog extends Model
{
    /** @use HasFactory<\Database\Factories\SmsDeliveryLogFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'external_id',
        'notifiable_type',
        'notifiable_id',
        'phone',
        'message',
        'notification_type',
        'status',
        'error_message',
        'response_data',
        'sent_at',
        'cost',
        'segments',
        'retry_count',
        'last_retry_at',
        'next_retry_at',
        'max_retries',
        'campaign_id',
    ];

    protected function casts(): array
    {
        return [
            'response_data' => 'array',
            'sent_at' => 'datetime',
            'cost' => 'decimal:4',
            'segments' => 'integer',
            'retry_count' => 'integer',
            'max_retries' => 'integer',
            'last_retry_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SmsCampaign::class);
    }

    public function markAsSent(array $responseData = []): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'response_data' => $responseData,
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function calculateCost(): float
    {
        $costPerSegment = config('services.texttango.cost_per_segment', 0.0075);

        return $this->segments * $costPerSegment;
    }

    public function canRetry(): bool
    {
        return $this->status === 'failed'
            && $this->retry_count < $this->max_retries;
    }

    public function scheduleRetry(): void
    {
        if (! $this->canRetry()) {
            return;
        }

        // Exponential backoff: 2^retry_count minutes
        $delayMinutes = 2 ** $this->retry_count;

        $this->update([
            'retry_count' => $this->retry_count + 1,
            'last_retry_at' => now(),
            'next_retry_at' => now()->addMinutes($delayMinutes),
        ]);
    }
}
