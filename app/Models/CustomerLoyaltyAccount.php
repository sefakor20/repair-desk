<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class CustomerLoyaltyAccount extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerLoyaltyAccountFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'customer_id',
        'loyalty_tier_id',
        'total_points',
        'lifetime_points',
        'enrolled_at',
        'tier_achieved_at',
    ];

    protected function casts(): array
    {
        return [
            'total_points' => 'integer',
            'lifetime_points' => 'integer',
            'enrolled_at' => 'datetime',
            'tier_achieved_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function loyaltyTier(): BelongsTo
    {
        return $this->belongsTo(LoyaltyTier::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function addPoints(int $points, string $type, ?string $description = null, ?array $metadata = null): LoyaltyTransaction
    {
        $this->total_points += $points;
        $this->lifetime_points += $points;
        $this->save();

        $this->checkAndUpdateTier();

        return $this->transactions()->create([
            'type' => $type,
            'points' => $points,
            'balance_after' => $this->total_points,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    public function deductPoints(int $points, string $type, ?string $description = null, ?array $metadata = null): LoyaltyTransaction
    {
        if ($points > $this->total_points) {
            throw new Exception('Insufficient points balance');
        }

        $this->total_points -= $points;
        $this->save();

        return $this->transactions()->create([
            'type' => $type,
            'points' => -$points,
            'balance_after' => $this->total_points,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    public function checkAndUpdateTier(): void
    {
        $eligibleTier = LoyaltyTier::active()
            ->where('min_points', '<=', $this->total_points)
            ->orderedByPriority()
            ->first();

        if ($eligibleTier && $eligibleTier->id !== $this->loyalty_tier_id) {
            $this->loyalty_tier_id = $eligibleTier->id;
            $this->tier_achieved_at = now();
            $this->save();
        }
    }

    public function getPointsMultiplier(): float
    {
        return $this->loyaltyTier?->points_multiplier ?? 1.0;
    }

    public function getDiscountPercentage(): float
    {
        return $this->loyaltyTier?->discount_percentage ?? 0.0;
    }
}
