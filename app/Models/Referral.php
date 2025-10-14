<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referral_code',
        'referred_email',
        'referred_name',
        'status',
        'points_awarded',
        'completed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'points_awarded' => 'integer',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'expired')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Generate a unique referral code for a customer.
     */
    public static function generateCode(Customer $customer): string
    {
        do {
            $code = mb_strtoupper(mb_substr($customer->first_name, 0, 3) . rand(1000, 9999));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Complete the referral and award points.
     */
    public function complete(int $pointsToAward = 500): void
    {
        if ($this->status !== 'pending') {
            return;
        }

        $this->status = 'completed';
        $this->completed_at = now();
        $this->points_awarded = $pointsToAward;
        $this->save();

        // Award points to referrer
        if ($this->referrer->loyaltyAccount) {
            $this->referrer->loyaltyAccount->addPoints(
                $pointsToAward,
                'referral',
                "Referral bonus for {$this->referred->full_name}",
                ['referral_id' => $this->id],
            );
        }
    }
}
