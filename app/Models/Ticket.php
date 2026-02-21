<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'ticket_number',
        'customer_id',
        'device_id',
        'branch_id',
        'problem_description',
        'diagnosis',
        'status',
        'priority',
        'assigned_to',
        'created_by',
        'estimated_completion',
        'actual_completion',
        'repair_completion_date',
        'post_repair_warranty_terms',
        'post_repair_warranty_expiry',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'estimated_completion' => 'datetime',
            'actual_completion' => 'datetime',
            'repair_completion_date' => 'date',
            'post_repair_warranty_expiry' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(TicketNote::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(TicketPart::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(DeviceAssessment::class);
    }

    public function isUnderRepairWarranty(): bool
    {
        if (! $this->post_repair_warranty_expiry) {
            return false;
        }

        return $this->post_repair_warranty_expiry->isFuture();
    }

    public function getRepairWarrantyStatusAttribute(): string
    {
        if (! $this->post_repair_warranty_expiry) {
            return 'No Repair Warranty';
        }

        if ($this->isUnderRepairWarranty()) {
            $daysLeft = (int) ceil(now()->diffInDays($this->post_repair_warranty_expiry, false));

            return "Active ({$daysLeft} days left)";
        }

        return 'Expired';
    }

    public function getRepairWarrantyDaysRemainingAttribute(): ?int
    {
        if (! $this->post_repair_warranty_expiry || ! $this->isUnderRepairWarranty()) {
            return null;
        }

        return (int) ceil(now()->diffInDays($this->post_repair_warranty_expiry, false));
    }

    protected static function boot(): void
    {
        parent::boot();

        // Apply branch scoping globally
        static::addGlobalScope(new BranchScoped());

        static::creating(function ($ticket): void {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TKT-' . mb_strtoupper(uniqid());
            }
        });
    }
}
