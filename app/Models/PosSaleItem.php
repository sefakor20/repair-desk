<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSaleItem extends Model
{
    /** @use HasFactory<\Database\Factories\PosSaleItemFactory> */
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'pos_sale_id',
        'inventory_item_id',
        'quantity',
        'unit_price',
        'subtotal',
        'line_discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'line_discount_amount' => 'decimal:2',
        ];
    }

    public function posSale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function getTotalAttribute(): float
    {
        return (float) ($this->subtotal - $this->line_discount_amount);
    }
}
