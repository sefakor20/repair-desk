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
        'sale_number',
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

    public function items(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
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
    }
}
