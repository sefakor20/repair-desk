<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InventoryStatus;
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
        'sku',
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

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function getTotalValueAttribute(): float
    {
        return (float) ($this->quantity * $this->cost_price);
    }
}
