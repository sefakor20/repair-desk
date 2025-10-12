<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosReturnItem extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'pos_return_id',
        'original_sale_item_id',
        'inventory_item_id',
        'quantity_returned',
        'unit_price',
        'subtotal',
        'line_refund_amount',
        'item_condition',
        'item_notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_returned' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'line_refund_amount' => 'decimal:2',
        ];
    }

    public function posReturn(): BelongsTo
    {
        return $this->belongsTo(PosReturn::class);
    }

    public function originalSaleItem(): BelongsTo
    {
        return $this->belongsTo(PosSaleItem::class, 'original_sale_item_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function calculateSubtotal(): void
    {
        $this->subtotal = $this->quantity_returned * $this->unit_price;
        $this->line_refund_amount = $this->subtotal;
    }
}
