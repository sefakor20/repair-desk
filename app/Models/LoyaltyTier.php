<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyTier extends Model
{
    /** @use HasFactory<\Database\Factories\LoyaltyTierFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
        'min_points',
        'points_multiplier',
        'discount_percentage',
        'color',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_points' => 'integer',
            'points_multiplier' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(CustomerLoyaltyAccount::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(LoyaltyReward::class, 'min_tier_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority')->orderBy('min_points');
    }
}
