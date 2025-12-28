<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReturnCondition;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnPolicy extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'return_window_days',
        'requires_receipt',
        'requires_original_packaging',
        'requires_approval',
        'restocking_fee_percentage',
        'minimum_restocking_fee',
        'refund_shipping',
        'allowed_conditions',
        'excluded_categories',
        'terms',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_receipt' => 'boolean',
            'requires_original_packaging' => 'boolean',
            'requires_approval' => 'boolean',
            'restocking_fee_percentage' => 'decimal:2',
            'minimum_restocking_fee' => 'decimal:2',
            'refund_shipping' => 'boolean',
            'allowed_conditions' => 'array',
            'excluded_categories' => 'array',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(PosSale::class);
    }

    public function isReturnEligible(PosSale $sale): bool
    {
        // Check if policy is active
        if (! $this->is_active) {
            return false;
        }

        // Check return window
        $daysSincePurchase = $sale->created_at->diffInDays(now());
        return $daysSincePurchase <= $this->return_window_days;
    }

    public function calculateRestockingFee(float $amount): float
    {
        $fee = ($amount * $this->restocking_fee_percentage) / 100;

        return max($fee, (float) $this->minimum_restocking_fee);
    }

    public function getAllowedConditionsLabels(): array
    {
        if (! $this->allowed_conditions) {
            return [];
        }

        return collect($this->allowed_conditions)
            ->map(fn($condition) => ReturnCondition::from($condition)->label())
            ->toArray();
    }
}
