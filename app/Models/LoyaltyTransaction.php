<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LoyaltyTransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\LoyaltyTransactionFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'customer_loyalty_account_id',
        'type',
        'points',
        'balance_after',
        'description',
        'reference_type',
        'reference_id',
        'metadata',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => LoyaltyTransactionType::class,
            'points' => 'integer',
            'balance_after' => 'integer',
            'metadata' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function loyaltyAccount(): BelongsTo
    {
        return $this->belongsTo(CustomerLoyaltyAccount::class, 'customer_loyalty_account_id');
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->latest()->limit($limit);
    }

    public function scopeByType($query, LoyaltyTransactionType $type)
    {
        return $query->where('type', $type);
    }
}
