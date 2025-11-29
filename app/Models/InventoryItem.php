<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InventoryStatus;
use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryItemFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'name',
        'branch_id',
        'sku',
        'barcode',
        'description',
        'category',
        'cost_price',
        'selling_price',
        'quantity',
        'reorder_level',
        'status',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'quantity' => 'integer',
            'reorder_level' => 'integer',
            'status' => InventoryStatus::class,
        ];
    }

    public function ticketParts(): HasMany
    {
        return $this->hasMany(TicketPart::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function isCriticallyLowStock(): bool
    {
        return $this->quantity <= ($this->reorder_level / 2);
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity === 0;
    }

    public function getStockPercentage(): float
    {
        if ($this->reorder_level === 0) {
            return 100;
        }

        return min(100, ($this->quantity / $this->reorder_level) * 100);
    }

    public function getTotalValueAttribute(): float
    {
        return (float) ($this->quantity * $this->cost_price);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level')
            ->where('quantity', '>', 0)
            ->where('status', InventoryStatus::Active);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0)
            ->where('status', InventoryStatus::Active);
    }

    protected static function boot(): void
    {
        parent::boot();

        // Apply branch scoping globally
        static::addGlobalScope(new BranchScoped());
    }
}
