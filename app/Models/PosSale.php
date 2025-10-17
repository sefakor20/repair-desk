<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\{PaymentMethod, PosSaleStatus};
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class PosSale extends Model
{
    /** @use HasFactory<\Database\Factories\PosSaleFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'shift_id',
        'return_policy_id',
        'sale_number',
        'branch_id',
        'customer_id',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_reference',
        'payment_status',
        'payment_metadata',
        'notes',
        'sold_by',
        'sale_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'sale_date' => 'datetime',
            'payment_method' => PaymentMethod::class,
            'payment_metadata' => 'array',
            'status' => PosSaleStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function soldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function returnPolicy(): BelongsTo
    {
        return $this->belongsTo(ReturnPolicy::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PosReturn::class, 'original_sale_id');
    }

    public function hasReturns(): bool
    {
        return $this->returns()->exists();
    }

    public function canBeReturned(): bool
    {
        if ($this->status !== PosSaleStatus::Completed) {
            return false;
        }

        if (! $this->returnPolicy) {
            return true; // No policy means always returnable
        }

        $daysSinceSale = $this->sale_date->diffInDays(now());

        return $daysSinceSale <= $this->returnPolicy->return_window_days;
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->sale_number)) {
                $sale->sale_number = 'POS-' . mb_strtoupper(uniqid());
            }
        });

        static::created(function ($sale) {
            if ($sale->customer_id && $sale->status === PosSaleStatus::Completed) {
                $sale->awardLoyaltyPoints();
            }
        });

        static::updated(function ($sale) {
            if ($sale->customer_id && $sale->wasChanged('status') && $sale->status === PosSaleStatus::Completed) {
                $sale->awardLoyaltyPoints();
            }
        });
    }

    public function awardLoyaltyPoints(): void
    {
        if (! $this->customer_id) {
            return;
        }

        $loyaltyAccount = $this->customer->loyaltyAccount()->firstOrCreate(
            ['customer_id' => $this->customer_id],
            ['enrolled_at' => now()],
        );

        $basePoints = (int) floor((float) $this->total_amount);
        $multiplier = $loyaltyAccount->getPointsMultiplier();
        $pointsToAward = (int) floor($basePoints * $multiplier);

        if ($pointsToAward > 0) {
            $loyaltyAccount->addPoints(
                $pointsToAward,
                'earned',
                "Purchase: {$this->sale_number}",
                [
                    'sale_id' => $this->id,
                    'sale_total' => $this->total_amount,
                    'base_points' => $basePoints,
                    'multiplier' => $multiplier,
                ],
            );
        }
    }
}
