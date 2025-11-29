<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use DateTimeInterface;

class SmsCampaign extends Model
{
    /** @use HasFactory<\Database\Factories\SmsCampaignFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'name',
        'message',
        'status',
        'segment_rules',
        'recipient_type',
        'contact_ids',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'failed_count',
        'estimated_cost',
        'actual_cost',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'segment_rules' => 'array',
            'contact_ids' => 'array',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'estimated_cost' => 'decimal:4',
            'actual_cost' => 'decimal:4',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(SmsDeliveryLog::class, 'campaign_id');
    }

    public function markAsScheduled(DateTimeInterface $scheduledAt): void
    {
        $this->update([
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
        ]);
    }

    public function markAsSending(): void
    {
        $this->update([
            'status' => 'sending',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_cost' => $this->deliveryLogs()->sum('cost'),
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }

    public function incrementSentCount(): void
    {
        $this->increment('sent_count');
    }

    public function incrementFailedCount(): void
    {
        $this->increment('failed_count');
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        return round((($this->sent_count + $this->failed_count) / $this->total_recipients) * 100, 1);
    }

    public function getSuccessRateAttribute(): float
    {
        $total = $this->sent_count + $this->failed_count;

        if ($total === 0) {
            return 0;
        }

        return round(($this->sent_count / $total) * 100, 1);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeSending($query)
    {
        return $query->where('status', 'sending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['scheduled', 'sending']);
    }
}
