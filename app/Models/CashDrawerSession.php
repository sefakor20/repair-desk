<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CashDrawerStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class CashDrawerSession extends Model
{
    /** @use HasFactory<\Database\Factories\CashDrawerSessionFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'opened_by',
        'closed_by',
        'opening_balance',
        'expected_balance',
        'actual_balance',
        'cash_sales',
        'cash_in',
        'cash_out',
        'discrepancy',
        'status',
        'opening_notes',
        'closing_notes',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'expected_balance' => 'decimal:2',
            'actual_balance' => 'decimal:2',
            'cash_sales' => 'decimal:2',
            'cash_in' => 'decimal:2',
            'cash_out' => 'decimal:2',
            'discrepancy' => 'decimal:2',
            'status' => CashDrawerStatus::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashDrawerTransaction::class);
    }

    public function calculateExpectedBalance(): float
    {
        return (float) ($this->opening_balance + $this->cash_sales + $this->cash_in - $this->cash_out);
    }

    public function calculateDiscrepancy(): float
    {
        if ($this->actual_balance === null) {
            return 0;
        }

        return (float) ($this->actual_balance - $this->expected_balance);
    }

    public function isOpen(): bool
    {
        return $this->status === CashDrawerStatus::Open;
    }

    public function isClosed(): bool
    {
        return $this->status === CashDrawerStatus::Closed;
    }
}
