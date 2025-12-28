<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Exception;

class PointTransfer extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'points',
        'message',
        'status',
        'completed_at',
        'sender_transaction_id',
        'recipient_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'recipient_id');
    }

    public function senderTransaction(): BelongsTo
    {
        return $this->belongsTo(LoyaltyTransaction::class, 'sender_transaction_id');
    }

    public function recipientTransaction(): BelongsTo
    {
        return $this->belongsTo(LoyaltyTransaction::class, 'recipient_transaction_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Process the point transfer.
     */
    public function process(): void
    {
        if ($this->status !== 'pending') {
            throw new Exception('Transfer already processed');
        }

        $senderAccount = $this->sender->loyaltyAccount;
        $recipientAccount = $this->recipient->loyaltyAccount;

        if (! $senderAccount || ! $recipientAccount) {
            $this->status = 'failed';
            $this->save();
            throw new Exception('Loyalty account not found');
        }

        if ($senderAccount->total_points < $this->points) {
            $this->status = 'failed';
            $this->save();
            throw new Exception('Insufficient points');
        }

        DB::transaction(function () use ($senderAccount, $recipientAccount): void {
            // Deduct from sender
            $senderTx = $senderAccount->deductPoints(
                $this->points,
                'transfer_sent',
                "Points transferred to {$this->recipient->full_name}",
                [
                    'transfer_id' => $this->id,
                    'recipient_id' => $this->recipient_id,
                    'message' => $this->message,
                ],
            );

            // Add to recipient
            $recipientTx = $recipientAccount->addPoints(
                $this->points,
                'transfer_received',
                "Points received from {$this->sender->full_name}",
                [
                    'transfer_id' => $this->id,
                    'sender_id' => $this->sender_id,
                    'message' => $this->message,
                ],
            );

            $this->sender_transaction_id = $senderTx->id;
            $this->recipient_transaction_id = $recipientTx->id;
            $this->status = 'completed';
            $this->completed_at = now();
            $this->save();
        });
    }

    /**
     * Cancel the transfer.
     */
    public function cancel(): void
    {
        if ($this->status !== 'pending') {
            throw new Exception('Can only cancel pending transfers');
        }

        $this->status = 'cancelled';
        $this->save();
    }
}
