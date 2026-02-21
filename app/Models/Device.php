<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceCategory;
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
        'device_type',
        'brand_id',
        'model_id',
        'color',
        'storage_capacity',
        'serial_number',
        'imei',
        'notes',
        'diagnosed_faults',
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
            'device_type' => DeviceCategory::class,
            'condition' => DeviceCondition::class,
            'diagnosed_faults' => 'array',
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

    public function deviceBrand(): BelongsTo
    {
        return $this->belongsTo(DeviceBrand::class, 'brand_id');
    }

    public function deviceModel(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class, 'model_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(DeviceAssessment::class);
    }

    public function checkInAssessment(): HasMany
    {
        return $this->hasMany(DeviceAssessment::class)->checkIn();
    }

    public function checkOutAssessment(): HasMany
    {
        return $this->hasMany(DeviceAssessment::class)->checkOut();
    }

    public function getDeviceNameAttribute(): string
    {
        // Use new relational data if available
        if ($this->deviceBrand && $this->deviceModel) {
            return $this->deviceBrand->name . ' ' . $this->deviceModel->name;
        }

        if ($this->deviceBrand) {
            return $this->deviceBrand->name . ($this->model ? ' ' . $this->model : '');
        }

        // Fallback to legacy text fields
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
            $daysLeft = (int) ceil(now()->diffInDays($this->warranty_expiry, false));
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
