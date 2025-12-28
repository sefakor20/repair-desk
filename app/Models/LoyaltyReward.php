<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LoyaltyRewardType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Exception;

class LoyaltyReward extends Model
{
    /** @use HasFactory<\Database\Factories\LoyaltyRewardFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
        'type',
        'points_required',
        'reward_value',
        'min_tier_id',
        'valid_from',
        'valid_until',
        'redemption_limit',
        'times_redeemed',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => LoyaltyRewardType::class,
            'points_required' => 'integer',
            'reward_value' => 'array',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'redemption_limit' => 'integer',
            'times_redeemed' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function minTier(): BelongsTo
    {
        return $this->belongsTo(LoyaltyTier::class, 'min_tier_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q): void {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q): void {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            });
    }

    public function scopeAvailable($query)
    {
        return $query->active()
            ->where(function ($q): void {
                $q->whereNull('redemption_limit')
                    ->orWhereRaw('times_redeemed < redemption_limit');
            });
    }

    public function canBeRedeemedBy(CustomerLoyaltyAccount $account): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($account->total_points < $this->points_required) {
            return false;
        }

        if ($this->min_tier_id && (! $account->loyalty_tier_id || $account->loyaltyTier->priority < $this->minTier->priority)) {
            return false;
        }

        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }
        return !($this->redemption_limit && $this->times_redeemed >= $this->redemption_limit);
    }

    public function redeem(CustomerLoyaltyAccount $account): LoyaltyTransaction
    {
        if (! $this->canBeRedeemedBy($account)) {
            throw new Exception('You are not eligible to redeem this reward.');
        }

        $transaction = $account->deductPoints(
            $this->points_required,
            'redeemed',
            "Redeemed: {$this->name}",
            [
                'reward_id' => $this->id,
                'reward_type' => $this->type->value,
                'reward_value' => $this->reward_value,
            ],
        );

        $this->increment('times_redeemed');

        return $transaction;
    }
}
