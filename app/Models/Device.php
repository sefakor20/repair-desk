<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceCondition;
use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Device extends Model
{
    /** @use HasFactory<\Database\Factories\DeviceFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'customer_id',
        'branch_id',
        'type',
        'brand',
        'model',
        'color',
        'storage_capacity',
        'serial_number',
        'imei',
        'notes',
        'condition',
        'condition_notes',
        'purchase_date',
        'warranty_expiry',
        'warranty_provider',
        'warranty_notes',
        'password_pin',
    ];

    protected function casts(): array
    {
        return [
            'condition' => DeviceCondition::class,
            'purchase_date' => 'date',
            'warranty_expiry' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DevicePhoto::class);
    }

    public function getDeviceNameAttribute(): string
    {
        $parts = array_filter([$this->brand, $this->model, $this->type]);

        return implode(' ', $parts) ?: 'Unknown Device';
    }

    public function isUnderWarranty(): bool
    {
        if (! $this->warranty_expiry) {
            return false;
        }

        return $this->warranty_expiry->isFuture();
    }

    public function getWarrantyStatusAttribute(): string
    {
        if (! $this->warranty_expiry) {
            return 'No Warranty Info';
        }

        if ($this->isUnderWarranty()) {
            $daysLeft = now()->diffInDays($this->warranty_expiry);
            return "Active ({$daysLeft} days left)";
        }

        return 'Expired';
    }

    public function getTotalRepairCostAttribute(): float
    {
        return (float) $this->tickets()
            ->whereHas('invoice')
            ->with('invoice')
            ->get()
            ->sum(fn($ticket) => $ticket->invoice?->total ?? 0);
    }

    public function getRepairCountAttribute(): int
    {
        return $this->tickets()->count();
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScoped());
    }
}
