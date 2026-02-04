<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ShiftStatus;
use App\Traits\{AutoAssignBranch, BranchScoped};
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Shift extends Model
{
    /** @use HasFactory<\Database\Factories\ShiftFactory> */
    use HasFactory;
    use HasUlids;
    use AutoAssignBranch;

    protected $fillable = [
        'branch_id',
        'shift_name',
        'opened_by',
        'closed_by',
        'status',
        'total_sales',
        'sales_count',
        'cash_sales',
        'card_sales',
        'mobile_money_sales',
        'bank_transfer_sales',
        'opening_notes',
        'closing_notes',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'total_sales' => 'decimal:2',
            'sales_count' => 'integer',
            'cash_sales' => 'decimal:2',
            'card_sales' => 'decimal:2',
            'mobile_money_sales' => 'decimal:2',
            'bank_transfer_sales' => 'decimal:2',
            'status' => ShiftStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(PosSale::class);
    }

    public function isOpen(): bool
    {
        return $this->status === ShiftStatus::Open;
    }

    public function isClosed(): bool
    {
        return $this->status === ShiftStatus::Closed;
    }

    public function duration(): ?int
    {
        if (! $this->ended_at) {
            return null;
        }

        return (int) round($this->started_at->diffInMinutes($this->ended_at));
    }

    public function averageSaleAmount(): float
    {
        if ($this->sales_count === 0) {
            return 0;
        }

        return (float) ($this->total_sales / $this->sales_count);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScoped());
    }
}
