<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

    protected function casts(): array
    {
        return [
            'response_data' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
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
}
