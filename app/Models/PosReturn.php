<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosReturn extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'return_number',
        'original_sale_id',
        'customer_id',
        'processed_by',
        'shift_id',
        'return_reason',
        'return_notes',
        'status',
        'subtotal_returned',
        'tax_returned',
        'restocking_fee',
        'total_refund_amount',
        'refund_method',
        'refund_reference',
        'refund_metadata',
        'refunded_at',
        'inventory_restored',
        'return_date',
    ];

    protected function casts(): array
    {
        return [
            'return_reason' => ReturnReason::class,
            'status' => ReturnStatus::class,
            'subtotal_returned' => 'decimal:2',
            'tax_returned' => 'decimal:2',
            'restocking_fee' => 'decimal:2',
            'total_refund_amount' => 'decimal:2',
            'refund_metadata' => 'array',
            'refunded_at' => 'datetime',
            'return_date' => 'datetime',
            'inventory_restored' => 'boolean',
        ];
    }

    public function originalSale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class, 'original_sale_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosReturnItem::class);
    }

    public function calculateTotals(?float $taxOverride = null): void
    {
        $this->subtotal_returned = $this->items->sum('subtotal');
        $this->tax_returned = $taxOverride ?? ($this->subtotal_returned * ($this->originalSale->tax_rate / 100));

        // Calculate restocking fee if applicable
        if ($this->return_reason->requiresRestockingFee() && $this->originalSale->returnPolicy) {
            $this->restocking_fee = $this->originalSale->returnPolicy->calculateRestockingFee((float) $this->subtotal_returned);
        } else {
            $this->restocking_fee = 0;
        }

        $this->total_refund_amount = ($this->subtotal_returned + $this->tax_returned) - $this->restocking_fee;
    }

    public function restoreInventory(): void
    {
        if ($this->inventory_restored) {
            return;
        }

        foreach ($this->items as $item) {
            $inventoryItem = $item->inventoryItem;
            $inventoryItem->increment('quantity', $item->quantity_returned);

            // Create inventory adjustment record
            InventoryAdjustment::create([
                'inventory_item_id' => $inventoryItem->id,
                'quantity_change' => $item->quantity_returned,
                'quantity_before' => $inventoryItem->quantity - $item->quantity_returned,
                'quantity_after' => $inventoryItem->quantity,
                'reason' => 'return',
                'notes' => "Return from sale #{$this->originalSale->sale_number} (Return #{$this->return_number})",
                'adjusted_by' => $this->processed_by,
            ]);
        }

        $this->update(['inventory_restored' => true]);
    }

    public function canBeProcessed(): bool
    {
        return $this->status->canRefund();
    }

    public function isWithinReturnWindow(): bool
    {
        $policy = $this->originalSale->returnPolicy;
        if (! $policy) {
            return true; // No policy means always allowed
        }

        $daysSinceSale = $this->originalSale->sale_date->diffInDays($this->return_date);

        return $daysSinceSale <= $policy->return_window_days;
    }

    public static function generateReturnNumber(): string
    {
        $prefix = 'RET';
        $date = now()->format('Ymd');
        $random = mb_strtoupper(mb_substr(uniqid(), -4));

        return "{$prefix}-{$date}-{$random}";
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('return_date', 'desc');
    }

    public function scopeByStatus($query, ReturnStatus $status)
    {
        return $query->where('status', $status);
    }
}
