<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
        'badge_icon',
        'badge_color',
        'type',
        'criteria',
        'points_reward',
        'is_active',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
            'points_reward' => 'integer',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_achievements')
            ->withPivot(['earned_at', 'is_displayed'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if customer has earned this achievement.
     */
    public function checkEligibility(Customer $customer): bool
    {
        $account = $customer->loyaltyAccount;
        if (! $account) {
            return false;
        }

        return match ($this->type) {
            'points_milestone' => $account->lifetime_points >= ($this->criteria['min_points'] ?? 0),
            'tier_reached' => $account->loyalty_tier_id && $account->loyaltyTier->name === ($this->criteria['tier_name'] ?? ''),
            'referral_count' => $customer->referralsMade()->completed()->count() >= ($this->criteria['referral_count'] ?? 0),
            'reward_redeemed' => $account->transactions()->where('type', 'redeemed')->count() >= ($this->criteria['redemption_count'] ?? 0),
            default => false,
        };
    }

    /**
     * Award achievement to customer.
     */
    public function awardTo(Customer $customer): void
    {
        if ($customer->achievements()->where('achievement_id', $this->id)->exists()) {
            return;
        }

        $customer->achievements()->attach($this->id, [
            'earned_at' => now(),
            'is_displayed' => true,
        ]);

        // Award bonus points if configured
        if ($this->points_reward > 0 && $customer->loyaltyAccount) {
            $customer->loyaltyAccount->addPoints(
                $this->points_reward,
                'achievement',
                "Achievement unlocked: {$this->name}",
                ['achievement_id' => $this->id],
            );
        }
    }
}
